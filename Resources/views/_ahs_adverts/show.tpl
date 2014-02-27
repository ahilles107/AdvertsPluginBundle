{{extends file="ahslayout.tpl"}}

{{ block title }}{{ $announcement->getName() }} - Ogłoszenia {{ $gimme->publication->name }} {{ /block }}

{{block head}}
    <meta property="og:title" content="{{ $announcement->getName() }} - Ogłoszenia w Międzyrzec Się Dzieje!" />
    <meta property="og:url" content="{{ generate_url route="ahs_advertsplugin_default_show" parameters=['id'=>$announcement->getId(), 'slug'=>$announcement->getSlug()] }}" />
    <meta property="og:description" content="{{ $announcement->getDescription()|truncate_utf8:120 }}" />
    {{ foreach from=$announcementPhotos item=photo }}
    <meta property="og:image" content="{{$photo['imageUrl']}}" />
    {{ /foreach }}
    <link href="/public/bundles/ahsadvertsplugin/css/bootstrap.min.css" rel="stylesheet">
    <link href="/public/bundles/ahsadvertsplugin/css/main.css" rel="stylesheet">
{{ /block }}

{{block body}}
{{ dynamic }}
<nav class="navbar navbar-inverse" role="navigation">
    <ol class="breadcrumb">
      <li><a href="#">Ogłoszenia</a></li>
      <li><a href="#">Dodaj nowe ogłoszenie</a></li>
    </ol>
</nav>

<div class="col-md-7">
    <a class="category-name" href="{{ generate_url route="ahs_advertsplugin_default_category" parameters=['id'=>$announcement->getCategory()->getId(), 'slug'=>$announcement->getCategory()->getSlug()] }}">{{ $announcement->getCategory()->getName() }}</a>
    <h2>{{ $announcement->getName() }}</h2>
    <div class="description">
        <p>{{ $announcement->getDescription() }}</p>
    </div>

    {{ if count($announcementPhotos) > 0}}
    <h3>Zdjęcia</h3>
    <ul class="announcement_photos">
        {{ foreach from=$announcementPhotos item=photo }}
            <li class="single_photo">
                <a rel="gallery" class="gallery_thumbnail" href="{{$photo['imageUrl']}}"><img src="{{$photo['thumbnailUrl']}}" /></a>
            </li>
        {{ /foreach }}
    </ul>
    {{ /if }}
    <div class="announcement-meta">
        <div class="fb-like" style="float:left; margin-right: 10px; min-width: 80px;" data-href="{{ generate_url route="ahs_advertsplugin_default_show" parameters=['id'=>$announcement->getId(), 'slug'=>$announcement->getSlug()] }}" data-send="false" data-layout="button_count" data-width="150" data-show-faces="false"></div>
        <div class="reads">Liczba wyświetleń: <span>{{ $announcement->getReads() }}</span></div>
    </div>
</div>
{{ /dynamic }}
{{/block}}

{{ block sidebar }}
{{ dynamic }}
<div class="aside col-md-5">
    <div class="announcementUser">
        <h4>Dodane przez:</h4>
        <img src="{{ include file="_tpl/user-image.tpl" user=$newscoopUser width=130 height=130 }}" />
        <p>{{ $newscoopUser->first_name }} {{ $newscoopUser->last_name }} <em>({{ $newscoopUser->uname }})</em></p>
        <ul class="user-info">
            <li>W serwisie od: <span> <abbr class="timeago" title="{{ $newscoopUser->created }}">{{ $newscoopUser->created }}</abbr></span></li>
            <li><a href="{{ $view->url(['username' => $newscoopUser->uname], 'user') }}">Zobacz pełny profil</a></li>
        </ul>
        <div style="clear:both"></div>
    </div>

    <h4>Ogłoszenie ważne do:</h4>
    <p class="valid_date"><span class="advert_valid_date" title="{{ $announcement->getValidDate()|date_format:"Y-m-d h:i:s" }}">{{ $announcement->getValidDate()|date_format:"Y-m-d" }}</span></p>

    <h4>Cena:</h4>
    <span class="price">{{ $announcement->getPrice() }} zł</span>

    {{ if $gimme->user->logged_in && $gimme->user->identifier == $announcement->getUser()->getNewscoopUserId() }}
    <a class="add_announcement" style="text-align: center; margin: 10px auto;" href="{{ generate_url route="ahs_advertsplugin_default_edit" parameters=['id'=>$announcement->getId()] }}">Edytuj</a>
    {{ /if }}
</div>
{{ /dynamic }}
{{ /block}}