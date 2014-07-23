{{ dynamic }}
{{ if count($announcementPhotos) > 0}}
<ul class="announcement_photos">
    {{ foreach from=$announcementPhotos item=photo }}
        <li class="single_photo">
            <img src="{{$photo['thumbnailUrl']}}" />
            <a href="#" class="remove_photo" data-id="{{$photo['announcementPhotoId']}}">usuń</a>
        </li>
    {{ /foreach }}
</ul>
{{ /if }}
{{ /dynamic }}