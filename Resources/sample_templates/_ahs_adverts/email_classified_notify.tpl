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
                                        <div>New classified has been added by user: <a href="">{{ $user->uname }}</a>
                                        <br>
                                        <a href="{{ $editLink }}">Edit classified</a>
                                        </div>
                                        <ul style="display:block;margin:15px 20px;padding:0;list-style:none;border-top:1px solid #eee">


                                            <li style="display:block;margin:0;padding:5px 0;border-bottom:1px solid #eee"><strong>Classified title:</strong> {{ $classified->getName() }}</li>
                                            <li style="display:block;margin:0;padding:5px 0;border-bottom:1px solid #eee"><strong>Classified description:</strong> {{ $classified->getDescription() }}</li>
                                            <li style="display:block;margin:0;padding:5px 0;border-bottom:1px solid #eee"><strong>Classified created at:</strong> {{ $created }}</li>
                                        </ul>

                                    </td>
                                </tr>
                            </tbody></table>

                        </td>
                    </tr>
                    <tr>
                        <td align="center" valign="top">

                            <table border="0" cellpadding="20" cellspacing="0" width="600">
                                <tbody><tr>
                                    <td align="center" valign="top">

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
{{ set_placeholder subject="Classifieds: New classified has been added" }}
{{/dynamic}}