{{dynamic}}
<center>
    <table style="background-color:#ffffff" border="0" cellpadding="20" cellspacing="0" height="100%" width="100%">
        <tbody><tr>
            <td align="center" valign="top">

                <table style="border-radius:6px;background-color:none" border="0" cellpadding="0" cellspacing="0" width="600">
                    <tbody>
                    <tr>
                        <td align="center" valign="top">

                            <table style="border-radius:6px;background-color:#ffffff" border="0" cellpadding="0" cellspacing="0" width="600">
                                <tbody><tr>

                                    <td style="line-height:150%;font-family:Georgia;font-size:14px;color:#000000;padding:20px" align="left" valign="top">
                                        <div>Hi {{ $user->uname }}!<br>You have received a new message from: <a href="mailto:{{ $params.email }}">{{ $params.email }}</a> on <strong><a href="{{ generate_url route="ahs_advertsplugin_default_show" parameters=['id'=>$announcement->getId()] }}"/>{{ $announcement->getName() }}</a></strong>
                                        <br>
                                        The following message is:
                                        </div>
                                        <ul style="display:block;margin:15px 20px;padding:0;list-style:none;border-top:1px solid #eee">


                                            <li style="display:block;margin:0;padding:5px 0;border-bottom:1px solid #eee"><strong>Subject:</strong> {{ $params.subject }}</li>
                                            <li style="display:block;margin:0;padding:5px 0;border-bottom:1px solid #eee"><strong>Message:</strong> {{ $params.message }}</li>
                                            {{ if isset($params.phone) }}
                                            <li style="display:block;margin:0;padding:5px 0;border-bottom:1px solid #eee"><strong>
                                            Phone no.:</strong> {{ $params.phone }}
                                            {{ /if }}
                                        </ul>

                                    </td>
                                </tr>
                            </tbody></table>

                        </td>
                    </tr>
                </tbody></table>

            </td>
        </tr>
    </tbody></table>
</center>
{{ set_placeholder subject="Classifieds: New message on {{ $announcement->getName() }}" }}
{{/dynamic}}