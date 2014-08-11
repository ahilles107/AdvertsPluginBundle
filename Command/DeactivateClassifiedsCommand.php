<?php

/*
 * This file is part of the Adverts Plugin.
 *
 * (c) Paweł Mikołajczuk <mikolajczuk.private@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package AHS\AdvertsPluginBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 */

namespace AHS\AdvertsPluginBundle\Command;

use Symfony\Component\Console;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * Deactivate classifieds when valid date expire
 */
class DeactivateClassifiedsCommand extends ContainerAwareCommand
{
    /**
     */
    protected function configure()
    {
        $this
        ->setName('classifieds:deactivate')
        ->setDescription('Deactivates classifieds when valid date expire');
    }

    /**
     */
    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
    {
        try {
            $em = $this->getContainer()->getService('em');
            $classifiedsService = $this->getContainer()->getService('ahs_adverts_plugin.ads_service');
            $now = new \DateTime();

            $qb = $em->getRepository('AHS\AdvertsPluginBundle\Entity\Announcement')
                ->createQueryBuilder('a');

            $qbRows = clone($qb);
            $qb
                ->select('count(a)')
                ->where("a.validTo < :now")
                ->andWhere('a.is_active = :status')
                ->setParameters(array(
                    'status' => true,
                    'now' => $now
                ))
                ->orderBy('a.created_at', 'desc');

            $announcementsCount = (int) $qb->getQuery()->getSingleScalarResult();

            $batch = 100;
            $steps = ($announcementsCount > $batch) ? ceil($announcementsCount / $batch) : 1;
            for ($i = 0; $i < $steps; $i++) {

                $offset = $i * $batch;

                $qbRows
                    ->where("a.validTo < :now")
                    ->andWhere('a.is_active = :status')
                    ->setParameters(array(
                        'status' => true,
                        'now' => $now
                    ))
                    ->orderBy('a.created_at', 'desc')
                    ->setFirstResult($offset)
                    ->setMaxResults($batch);

                $expiredAnnouncements = $qbRows->getQuery()->getResult();

                foreach ($expiredAnnouncements as $announcement) {
                    $classifiedsService->deactivateClassified($announcement);
                }
            }

            if ($input->getOption('verbose')) {
                $output->writeln('<info>Finished... '.$announcementsCount.' classifieds deactivated...</info>');
            }

        } catch (\Exception $e) {
            if ($input->getOption('verbose')) {
                $output->writeln('<error>Error occured: '.$e->getMessage().'</error>');
            }

            return false;
        }
    }
}
