{{extends file="ahslayout.tpl"}}

{{ block title }}Ogłoszenia - {{ $gimme->publication->name }} {{ /block }}

{{block head}}
    <!-- Bootstrap core CSS -->
    <link href="/public/bundles/ahsadvertsplugin/css/bootstrap.min.css" rel="stylesheet">
    <link href="/public/bundles/ahsadvertsplugin/css/main.css" rel="stylesheet">
    <script src="/public/bundles/ahsadvertsplugin/js/plupload/js/plupload.full.js"></script>
    <meta property="og:type" content="article" />
{{/block}}

{{block body}}
{{ dynamic }}
<nav class="navbar navbar-inverse" role="navigation">
    <ol class="breadcrumb">
      <li><a href="#">Ogłoszenia</a></li>
      <li><a href="#">Dodaj nowe ogłoszenie</a></li>
    </ol>
</nav>

<div class="col-md-7">
    {{ if count($errors) > 0 }}
    <ul>
        {{ foreach from=$errors item=error }}
            <li class="error_message">{{ $error['message'] }}</li>
        {{ /foreach }}
    </ul>
    {{ /if }}

    <form action="{{$form_path}}" method="post" >
        
        <div style="margin:8px 4px;">
            <label style="display: block; font-size: 20px; font-weight: bold; margin-bottom: 10px;">Tytuł</label>
            <input style="font-size: 20px;padding: 4px;height: 25px;width: 550px;" type="text" name="announcement[name]" value="{{ $announcement->getName() }}" placeholder="Podaj tytuł" />
        </div>

        <div style="margin:8px 4px;">
            <label style="display: block; font-size: 20px; font-weight: bold; margin-bottom: 10px;">Kategoria</label>
            <select style="padding: 5px; min-width: 250px;" name="announcement[category]">
                {{ foreach from=$categories item=category }}
                    <option  {{ if $announcement->getCategory() != null }}{{ if $announcement->getCategory()->getId() == $category->getId() }}selected="selected"{{ /if }}{{ /if }} value="{{$category->getId()}}">{{$category->getName()}}</option>
                {{ /foreach }}
            </select> 
        </div>

        <div style="margin:8px 4px;">
            <textarea name="announcement[description]" style="width: 600px; height: 200px; padding: 5px; font-size: 19px;">{{ $announcement->getDescription() }}</textarea>
        </div>

        <div style="margin:8px 4px;">
            <label style="display: block; font-size: 20px; font-weight: bold; margin-bottom: 10px;">Cena</label>
            <input style="font-size: 20px;padding: 4px;height: 25px;width: 150px;" type="text" name="announcement[price]" value="{{ $announcement->getPrice() }}" placeholder="Podaj cenę" /> zł
        </div>
        {{if $type == 'add'}}
        <div style="margin:8px 4px;">
            <input style="padding:4px;" type="checkbox" name="request[rules]" value="1" />
            <label>Akceptuję <a href="http://miedzyrzecsiedzieje.dev/pl/static/51/267/Regulamin-regulamin.htm">regulamin</a> strony</label>
        </div>
        {{/if}}
        <input type="submit" value="{{if $type == 'add'}}Dodaj ogłoszenie{{else}}Zapisz zmiany{{/if}}" style="float: right;width: 225px;height: 50px;font-family: 'PT Sans',Arial,Helvetica,sans-serif;font-size: 20px;font-weight: bold;text-transform: uppercase;color: rgb(255, 255, 255);background: none repeat scroll 0% 0% rgb(34, 34, 34);border: 0px none;" />
    </form>
</div>
{{ /dynamic }}
{{/block}}

{{ block sidebar }}
<div class="col-md-5">
    <div class="aside">
        <p class="valid_date">Ogłoszenie będzie ważne do: <span class="advert_valid_date" title="{{ $announcement->getValidDate()|date_format:"Y-m-d h:i:s" }}">{{ $announcement->getValidDate()|date_format:"Y-m-d" }}</span> </p>

        <h2 class="photo_upload">Dodaj zdjęcia</h2>
        <div class="upload_photos">
            <ul>
                <li class="input-file red" id="plupload-container">
                    <div class="show-value"></div>
                    <input type="file" class="upload" id="plupload-choose-file" />
                </li>
                <li>
                    <p>Akceptowane pliki: Zdjęcia (jpg, png, gif)<br>
                </li>
            </ul>
        </div>
        <div id="addedPhotos"></div>
    </div>

    <script type="text/javascript">
    $(document).ready(function(){
        var uploader = new plupload.Uploader({
            runtimes : 'html5,flash,silverlight',
            browse_button : 'plupload-choose-file',
            container : 'plupload-container',
            max_file_size : '10mb',
            url : '{{ generate_url route="ahs_advertsplugin_default_uploadphoto" }}',
            flash_swf_url : '{{ $view->baseUrl("/js/plupload/js/plupload.flash.swf") }}',
            silverlight_xap_url : '{{ $view->baseUrl("/js/plupload/js/plupload.silverlight.xap") }}',
            filters : [
                {title : "Zdjęcia", extensions : "jpg,gif,png,JPG,GIF,PNG"},
            ]
        });

        uploader.init();

        uploader.bind('FilesAdded', function(up, files) {
            $('div.show-value').html('Wgrywanie...');
            up.start();

            up.refresh(); // Reposition Flash/Silverlight
        });

        uploader.bind('FileUploaded', function(up, file, info) {
            $('div.show-value').html('Wgrano!');
            var response = info['response'];
            $('#addedPhotos').html(response);

            up.refresh();
        });

        var loadPhotos = function(){
            $.ajax({
              url: '{{ generate_url route="ahs_advertsplugin_default_renderphotos" }}',
              dataType: 'html'
            }).done(function(res) {
                $('#addedPhotos').html(res);
            });
        }

        loadPhotos();

        $('a.remove_photo').live('click', function(e){
            e.preventDefault();
            var removeId = $(this).data('id');
            $.ajax({
              url: '{{ generate_url route="ahs_advertsplugin_default_removephoto" }}',
              type: "POST",
              dataType: 'html',
              data: {'id': removeId}
            }).done(function(res) {
                $('#addedPhotos').html(res);
            });
        });
    });
    </script>
</div>
{{ /block}}