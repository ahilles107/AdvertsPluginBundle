{{ dynamic }}
{{ if isset($errors) && count($errors) > 0 }}
<ul>
    {{ foreach from=$errors item=error }}
        <li style="color: #990000; background:#FFEDED; border-color:#EEA4B0; list-style: none; padding: 6px;">
            {{ $error }}
        </li>
    {{ /foreach }}
</ul><br>
{{ /if }}
{{ if isset($result) && $result }}
<ul class="message-div">
    <li style="color: #3c763d;background: #dff0d8;border-color: #d6e9c6;list-style: none;padding: 6px;">Wgrano!</li><br>
</ul>
{{ /if }}
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