{{extends file="ahslayout.tpl"}}

{{ block title }} {{ $currentCategory->getName()}} - {{ $gimme->publication->name }} {{ /block }}

{{block head}}
  <meta property="og:title" content="{{ $currentCategory->getName()}} - Ogłoszenia w Międzyrzec Się Dzieje!" />
  <meta property="og:url" content="{{ generate_url route="ahs_advertsplugin_default_category" parameters=['id'=>$currentCategory->getId(), 'slug'=>$currentCategory->getSlug()] }}" />
  <meta property="og:description" content="Ogłoszenia z kategorii '{{ $currentCategory->getName()}}' - Międzyrzec Się Dzieje, najlepsze ogłoszenia w mieście!" />
  <link href="/public/bundles/ahsadvertsplugin/css/bootstrap.min.css" rel="stylesheet">
  <link href="/public/bundles/ahsadvertsplugin/css/main.css" rel="stylesheet">
{{ /block }}

{{block body}}
{{ dynamic }}
<nav class="navbar navbar-inverse" role="navigation">
    <ol class="breadcrumb">
      <li><a href="#">Ogłoszenia</a></li>
      <li><a href="#">{{ $currentCategory->getName()}}</a></li>
    </ol>
</nav>
    <div class="col-md-7">
        <ul id="announcements-list">
        {{ render file="_ahs_adverts/_tpl/anouncements_list.tpl" announcementsList=$announcementsList }}
        </ul>
        {{ $announcementsPagination }}
    </div>
{{ /dynamic }}
{{/block}}

{{ block sidebar }}
<div class="aside col-md-5">
    <div class="aside">
        <a class="add_announcement" href="{{ generate_url route="ahs_advertsplugin_default_add" }}">Dodaj ogłoszenie</a>

        {{ include "_ahs_adverts/_tpl/categories_list.tpl" }}
    </div>
</div>
{{ /block}}