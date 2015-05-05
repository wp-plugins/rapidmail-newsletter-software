<?php

    if (!empty($rm_options['subscription_form_url'])) {

        $url = $rm_options['subscription_form_url'];

        preg_match('/^http.*?\/(\d+)\/(\d+)\/(.*?)\/(.*?)\/(.*?)\.html$/', $url, $matches);

?>

        <form class="layout_form rm_form rm_font" action="<?php echo $url; ?>" method="post" target="_blank">
            <input type="hidden" name="olduri" value="0" />
            <input type="hidden" name="data_db_id" value="<?php echo @$matches[1]; ?>" />
            <input type="hidden" name="recipientlist_id" value="<?php echo @$matches[2]; ?>" />
            <input type="hidden" name="checksum" value="<?php echo @$matches[3]; ?>" />

            <div rel="email" class="" style=" margin-bottom:15px;">
                <label for="rm_email" class="itemname">E-Mail*</label><br />
                <input id="rm_email" name="email" type="text" value="" style="">
            </div>
            <div rel="text" class="" style="margin-bottom:15px;">
                <label for="rm_firstname" class="itemname">Vorname</label><br />
                <input id="rm_firstname" name="firstname" value="" type="text" style="">
            </div>
            <div rel="text" class="" style="margin-bottom:15px;">
                <label for="rm_lastname" class="itemname">Nachname</label><br />
                <input id="rm_lastname" name="lastname" value="" type="text" style="">
            </div>
            <div rel="radio" class="" style="margin-bottom:15px;">
                <label class="itemname">Geschlecht</label><br />
                <input type="radio" id="rm_gender_female" name="gender" value="female" > <label for="rm_gender_female">weiblich</label>
                <input type="radio" id="rm_gender_male" name="gender" value="male" > <label for="rm_gender_male">männlich</label>
            </div>
            <div rel="radio" class="" style="margin-bottom:15px;">
                <label class="itemname">Sendeformat</label><br />
                <input type="radio" id="rm_text" name="mailtype" value="text" > <label for="rm_text">Text</label>
                <input type="radio" id="rm_html" name="mailtype" value="html" > <label for="rm_html">HTML</label>
            </div>

            <div rel="button" class="" style="text-align:center; margin-bottom:px;">
                <button type="submit" class="" value="Anmelden">Anmelden</button>
            </div>

        </form>

<?php

    }

?>
