{{extends file="ahslayout.tpl"}}

{{ block title }}Ogłoszenia - {{ $gimme->publication->name }} {{ /block }}

{{block body}}
<ol class="breadcrumb">
  <li><a href="#">Ogłoszenia</a></li>
  <li><a href="#">Lista Ogłoszeń</a></li>
</ol>

<div class="clearfix"></div>
<div class="col-md-7">
    {{ include file="_ahs_adverts/_tpl/anouncements_list.tpl" }}
</div>
{{/block}}


{{ block sidebar }}
<div class="aside col-md-5">
    <div class="aside">
        <a class="add_announcement" href="{{ generate_url route="ahs_advertsplugin_default_add" }}">Dodaj ogłoszenie</a>
        
        {{ include "_ahs_adverts/_tpl/categories_list.tpl" }}
    </div>
</div>
{{ /block}}