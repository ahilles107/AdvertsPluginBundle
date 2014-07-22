<ul id="announcements-list">
{{ list_announcements length=10 order="created_at desc" }}
    {{ $announcement = $gimme->announcement }}

	<li class="number-{{ $announcement->getCategory()->getId() }}">
		{{ $firstImage = $announcement->getFirstImage() }}
		<div style="float:left">
			{{ if $announcement->getImages()|count > 0 }}
			<img src="{{ $firstImage['thumbnailUrl'] }}" alt="{{$announcement->getName()}}"/>
			{{ /if }}
			{{ if $announcement->getImages()|count > 1 }}<div style="text-align: center; margin-top: -12px; margin-bottom: 5px; font-size: 12px;">Zdjęć: <span> {{ $announcement->getImages()|count }}</span></div>{{ /if }}
		</div>
		<a href="{{ generate_url route="ahs_advertsplugin_default_show" parameters=['id'=>$announcement->getId(), 'slug'=>$announcement->getSlug()] }}" class="announcement-name">{{ $announcement->getName() }}</a> 
		<p>{{ $announcement->getDescription()|truncate_utf8:120 }}</p>
		<div style="clear:both"></div>
		<div class="meta">
			<div class="meta-info"><span><a href="{{ generate_url route="ahs_advertsplugin_default_category" parameters=['id'=>$announcement->getCategory()->getId(), 'slug'=>$announcement->getCategory()->getSlug()] }}">{{ $announcement->getCategory()->getName() }}</a></span> | </div>
			{{ if $announcement->getPrice() > 0 }}<div class="meta-info">Cena: <span>{{  $announcement->getPrice() }} zł</span></div>{{ /if }}
			<div class="meta-info">Ważne do: <span>{{ $announcement->getValidTo()|date_format:"Y-m-d" }}</span></div>
			<div class="meta-info">Wyświetleń: <span>{{ $announcement->getReads() }}</span></div>
			<div style="clear:both"></div>
		</div>
	</li>
	<div style="clear:both"></div>
{{if $gimme->current_list->at_end}}
</ul>
{{ /if }}

	{{ listpagination }}
{{ /list_announcements }}