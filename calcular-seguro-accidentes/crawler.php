<?php

// Make sure the script can handle large folders/files
ini_set('max_execution_time', 600);
ini_set('memory_limit', '1024M');

include_once('lib/simplehtmldom/simple_html_dom.php');
require(dirname(__FILE__) . '/wp-blog-header.php');

set_time_limit(0);// 0 is infite limit

echo 'Variables globales: '."<br>";
echo '&nbsp;&nbsp;WP_SITEURL: ' . WP_SITEURL."<br>";
echo '&nbsp;&nbsp;TC: ' . TC . "<br>";
echo '&nbsp;&nbsp;HEADER_NAME: ' . HEADER_NAME . "<br>";
echo '&nbsp;&nbsp;FOOTER_NAME: ' . FOOTER_NAME . "<br>";

echo "Empieza proceso ...<br>";
echo "Moviendo pages.route.js de wp-content/themes/Divi-Poc/pages.route.js a " . TC . "app/pages/pages.route.js" . "<br>";
copyRecursively('wp-content/themes/Divi-Poc/pages.route.js', TC . 'app/pages/pages.route.js');
$page_id = $_GET['id'];
$final = $_GET['final'];

if ($page_id != null) {
    echo "<br>" . "----------" . "<br>";
    $permalink = get_page_link($page_id);
    echo "URL: " . $permalink . "<br>";;
    generatePageFiles($permalink);
    echo "<br>" . "----------" . "<br>";

    if ($final) {
        // Get cURL resource
        $curl = curl_init();
        echo "Se publicará en final";
        echo "<br>";
        echo "El curl es " . $curl;
        echo "<br>" . "----------" . "<br>";
        // Set some options - we are passing in a useragent too here
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => WP_SITEURL .'/final.php?id=' . $page_id,
            CURLOPT_USERAGENT => 'cURL Request'
        ]);
        // Send the request & save response to $resp
        $resp = curl_exec($curl);

        echo $resp;// Close request to clear up some resources
        curl_close($curl);
        echo "<br>_______<br>";

    } else {
        if(basename($permalink) != "assets" && basename($permalink) != "mocks") {
            $urlPage = get_site_url() . "/" . TC . "#" . "/" . basename($permalink);
            $seconds = 5;
            echo "<br>" . "Redirección a <b>" . $urlPage . "</b> en " . $seconds . " segundos" . "<br>";
            header("Refresh: $seconds; URL=$urlPage");
        }
    }
} else {
    $posts = new WP_Query('post_type=page&posts_per_page=-1&post_status=publish');
    $posts = $posts->posts;
    header('Content-type:text/plain');
    foreach ($posts as $post) {
        switch ($post->post_type) {
            case 'page':
                $permalink = get_page_link($post->ID);
                break;
            default:
                break;
        }
        echo "\n" . "URL: " . $permalink . "\n";;
        generatePageFiles($permalink);
        echo "\n" . "----------" . "\n";
    }
    echo "\n" . "----------" . "\n";
}

echo "Proceso finalizado correctamente";

function generatePageFiles($permalink)
{
    $html = curl_get_contents($permalink);

    if ($html) {
        // TODO: formatear todo
        if (sizeof($html->find('mfc-layout div.et_pb_section')) > 1) {
            $content = "";
            foreach ($html->find('mfc-layout div.et_pb_section') as $i => $element) {
                if ($i == 0) {
                    $content .= '<mfc-layout>
                    <section class="mfc-layout__main-container__content">';
                    $cont = $element->outertext;
                    $content .= $cont;
                    $content .= '</section>';
                } else {
                    $content .= '<aside class="mfc-layout__main-container__aside">';

                    $cont = $element->outertext;
                    $content .= $cont;
                    $content .= '</aside>';
                }
            }
            $content .= '</mfc-layout>';
        } else {
            foreach ($html->find('mfc-layout') as $element)
                $content = $element->outertext;
        }

        //GRIDS

        foreach ($html->find('mfc-layout div.et_pb_row') as $i => $element) {
            $a = $element->outertext;
            $b = $element->innertext;
            if (strpos($b, 'et_pb_column_1_2') != false || strpos($b, 'et_pb_column_1_3') != false
                || strpos($b, 'et_pb_column_1_4') != false || strpos($b, 'et_pb_column_1_6') != false) {
                $content_mod = str_replace("et_pb_row ", 'et_pb_row mfc-grid-12 ', $a);
            } else if (strpos($b, 'et_pb_column_1_5') != false || strpos($b, 'et_pb_column_2_5') != false
                || strpos($b, 'et_pb_column_3_5') != false) {
                $content_mod = str_replace("et_pb_row ", 'et_pb_row mfc-grid-5 ', $a);
            } else {
                $content_mod = str_replace("et_pb_row ", 'et_pb_row mfc-grid-12 ', $a);
            }
            $str = str_get_html($content_mod);
            $first = false;
            $off_class = '';
            foreach ($str->find('div.et_pb_column') as $j => $inside) {
                $out = $inside->outertext;
                $out_mod = $out;
                if ($off_class != "") {
                    $out_mod = str_replace('et_pb_column ', $off_class . ' et_pb_column ', $out_mod);
                    $off_class = '';
                }
                if (strpos($out, 'et_pb_column_empty') != false) {
                    preg_match_all('/et_pb_column_([^"]+)/', $out, $m);
                    if ($m) {
                        $off_class = 'has_offset_' . explode(' ', $m[1][0])[0];
                    }
                }
                if (strpos($out, 'et_pb_column_empty') == false && !$first) {
                    $first = true;
                    $out_mod = str_replace('et_pb_column ', 'first_column et_pb_column ', $out_mod);
                }
                $content_mod = str_replace($out, $out_mod, $content_mod);

            }
            $content_mod = str_replace('<div', '<sssss', $content_mod);
            $content_mod = str_replace('</div', '</sssss', $content_mod);
            $content = str_replace($a, $content_mod, $content);
        }
        $content = preg_replace('/\<[\/]{0,1}div[^\>]*\>/i', '', $content);
        $content = str_replace('<sssss', '<div', $content);
        $content = str_replace('</sssss', '</div', $content);

        //Quitamos espacios múltiples
        //$content = preg_replace('/\s\s+/', '', $content);
        /*Añadir nombre de todos los componentes (menos header y footer)*/
        $components = array("mfc-header", "mfc-footer", "mfc-step-diagram-multi",
            "mfc-card", "mfc-vertical-list", "mfc-group-condition",
            "mfc-shadow-box", "mfc-background-image", "mfc-banner",
            "mfc-block", "mfc-contract-data-summary", "mfc-contract-title",
            "mfc-coverages-list", "mfc-coverage-info", "mfc-coverage-custom",
            "mfc-conditions-accept", "mfc-standard-date", "mfc-standard-button",
            "mfc-wait-layer", "mfc-deyde", "mfc-horizontal-list",
            "mfc-bank-account", "mfc-hidden-field", "mfc-exit-intent",
            "mfc-folding-box", "mfc-folding-text", "mfc-help-field-column",
            "mfc-link-button", "mfc-mandatory-field", "mfc-matrix-list",
            "mfc-important-content", "mfc-important-info", "mfc-link-text",
            "mfc-number-field", "mfc-number-step", "mfc-option-selected",
            "mfc-password", "mfc-price-box", "mfc-price-summary",
            "mfc-print-button", "mfc-prompt-list", "mfc-prompt-select",
            "mfc-prompt-text", "mfc-quote-card", "mfc-reset-button",
            "mfc-riched-content", "mfc-sort-date", "mfc-standard-link",
            "mfc-standard-range", "mfc-dynamic-fields", "mfc-dynamic-fields-list",
            "mfc-standard-select", "mfc-standard-text", "mfc-standard-text-area",
            "mfc-calendar-date", "mfc-check-box", "mfc-filter-input",
            "mfc-wait-info", "mfc-table", "mfc-upload-file",
            "mfc-step-diagram-form", "mfc-calendar", "mfc-ccc-code",
            "mfc-check-box-list", "mfc-check-box-list-sigortasi", "mfc-comparator-deductible",
            "mfc-compare-table", "mfc-edit-data", "mfc-google-address",
            "mfc-identification-number", "mfc-payment", "mfc-pension-simulation",
            "mfc-price-model-equity", "mfc-price-model-list", "mfc-iban-code",
            "mfc-price-model-mapfre", "mfc-recaptcha", "mfc-standard-page");
        $str = str_get_html($content);
        if ($str != false) {
            $existing_components = [];
            foreach ($components as $component) {
                $str = str_get_html($content);
                foreach ($str->find($component) as $com) {
                    array_push($existing_components, $component);
                    $single_com = $com->outertext;
                    $content = str_replace($single_com, '', $content);
                }
            }
        }

        //Replace -com-hidden por ""
        $components_hidden = array("mfc-header-comp-hidden", "mfc-footer-comp-hidden", "mfc-step-diagram-multi-comp-hidden",
            "mfc-card-comp-hidden", "mfc-vertical-list-comp-hidden", "mfc-group-condition-comp-hidden",
            "mfc-shadow-box-comp-hidden", "mfc-background-image-comp-hidden", "mfc-banner-comp-hidden",
            "mfc-block-comp-hidden", "mfc-contract-data-summary-comp-hidden", "mfc-contract-title-comp-hidden",
            "mfc-coverages-list-comp-hidden", "mfc-coverage-info-comp-hidden", "mfc-coverage-custom-comp-hidden",
            "mfc-conditions-accept-comp-hidden", "mfc-standard-date-comp-hidden", "mfc-standard-button-comp-hidden",
            "mfc-wait-layer-comp-hidden", "mfc-deyde-comp-hidden", "mfc-horizontal-list-comp-hidden",
            "mfc-bank-account-comp-hidden", "mfc-hidden-field-comp-hidden", "mfc-exit-intent-comp-hidden",
            "mfc-folding-box-comp-hidden", "mfc-folding-text-comp-hidden", "mfc-help-field-column-comp-hidden",
            "mfc-link-button-comp-hidden", "mfc-mandatory-field-comp-hidden", "mfc-matrix-list-comp-hidden",
            "mfc-important-content-comp-hidden", "mfc-important-info-comp-hidden", "mfc-link-text-comp-hidden",
            "mfc-number-field-comp-hidden", "mfc-number-step-comp-hidden", "mfc-option-selected-comp-hidden",
            "mfc-password-comp-hidden", "mfc-price-box-comp-hidden", "mfc-price-summary-comp-hidden",
            "mfc-print-button-comp-hidden", "mfc-prompt-list-comp-hidden", "mfc-prompt-select-comp-hidden",
            "mfc-prompt-text-comp-hidden", "mfc-quote-card-comp-hidden", "mfc-reset-button-comp-hidden",
            "mfc-riched-content-comp-hidden", "mfc-sort-date-comp-hidden", "mfc-standard-link-comp-hidden",
            "mfc-standard-range-comp-hidden", "mfc-dynamic-fields-comp-hidden", "mfc-dynamic-fields-list-comp-hidden",
            "mfc-standard-select-comp-hidden", "mfc-standard-text-comp-hidden", "mfc-standard-text-area-comp-hidden",
            "mfc-calendar-date-comp-hidden", "mfc-check-box-comp-hidden", "mfc-filter-input-comp-hidden",
            "mfc-wait-info-comp-hidden", "mfc-table-comp-hidden", "mfc-upload-file-comp-hidden",
            "mfc-step-diagram-form-comp-hidden", "mfc-calendar-comp-hidden", "mfc-ccc-code-comp-hidden",
            "mfc-check-box-list-comp-hidden", "mfc-check-box-list-sigortasi-comp-hidden", "mfc-comparator-deductible-comp-hidden",
            "mfc-compare-table-comp-hidden", "mfc-edit-data-comp-hidden", "mfc-google-address-comp-hidden",
            "mfc-identification-number-comp-hidden", "mfc-payment-comp-hidden", "mfc-pension-simulation-comp-hidden",
            "mfc-price-model-equity-comp-hidden", "mfc-price-model-list-comp-hidden", "mfc-iban-code-comp-hidden",
            "mfc-price-model-mapfre-comp-hidden", "mfc-recaptcha-comp-hidden", "mfc-standard-page-comp-hidden");

        $content = str_replace($components_hidden, $components, $content);

        $content = addCustomClass($content, "1", "2", "6");
        $content = addCustomClass($content, "1", "3", "4");
        $content = addCustomClass($content, "2", "3", "8");
        $content = addCustomClass($content, "1", "4", "3");
        $content = addCustomClass($content, "3", "4", "9");
        $content = addCustomClass($content, "1", "5", "1");
        $content = addCustomClass($content, "1", "6", "2");
        $content = addCustomClass($content, "2", "5", "2");
        $content = addCustomClass($content, "3", "5", "3");
        $content = addCustomClass($content, "4", "4", "");

        //Quitamos espacios múltiples
        $content = preg_replace('/\s\s+/', '', $content);

        //QUITAR tabulaciones que dan error en el json
        $content = str_replace("\t", '    ', $content);

        //ELIMINAR DE LOS MFC-PROPERTIES CAMPOS VACÍOS
        foreach ($existing_components as $component) {
            $str = str_get_html($content);
            foreach ($str->find($component) as $com) {
                $single_com = $com->outertext;

                preg_match_all('/\<' . $component . ' mfc-properties="(.*?)">\<\/' . $component . '\>/s', $single_com, $match);

                $p = "";
                if ($match[1]) {
                    $p = $match[1][0];
                }
                $data = str_replace("'", '"', $p);
                $json_data = json_decode($data, true);
                $filteredData = array_filter_recursive($json_data);
                $filteredJson = json_encode($filteredData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                $end_data = str_replace('"', "'", $filteredJson);
                $content = str_replace($p, $end_data, $content);
                //QUITAR ESPACIOS múltiples y saltos de línea
                $content = preg_replace('/\s\s+/', '', $content);
                $content = str_replace("\n", "", $content);
                //QUITAR ESPACIO DESPUÉS DE ":"
                $content = preg_replace('/:\s/', ':', $content);
            }
        }

        $str = str_get_html($content);
        //Header
//        foreach ($str->find("mfc-header") as $com) {
//            $header_com = $com->outertext;
//            $file_name = TC . 'app/pages/' . HEADER_NAME . '.html';
//            file_put_contents($file_name, $header_com);
//            //echo "generated header";
//            $content = str_replace($header_com, '', $content);
//        }

        // //Footer
        // foreach ($str->find("mfc-footer") as $com) {
        //     $footer_com = $com->outertext;
        //     $file_name = TC . 'app/pages/' . FOOTER_NAME . '.html';
        //     file_put_contents($file_name, $footer_com);
        //     //echo "generated footerrrr";
        //     $content = str_replace($footer_com, '', $content);
        // }

        //Quitamos etiqueta </main>
        $content = preg_replace('/<\/main>/', '', $content);

        // Quitamos comentarios innecesarios de divi
        $content = preg_replace('/<!--(.|\s)*?-->/', '', $content);

        //Quitamos códigi Divi del final
        $content = substr($content, 0, strrpos($content, '</mfc-layout>')) . '</mfc-layout>';

        if (basename($permalink) == "config") {
            //Limpiamos divs de divi
            $content = preg_replace('/\<[\/]{0,1}div[^\>]*\>/i', '', $content);
            $content = str_replace('<mfc-layout><section class="mfc-layout__main-container__fullwidth">', '', $content);
            $content = str_replace('</section></mfc-layout>', '', $content);
            //Nombre del fichero:
            $file_name = TC . 'app/pages/' . basename($permalink) . '.json';
            file_put_contents($file_name, $content);
        } else if (basename($permalink) == "index") {
            //Limpiamos divs de divi
            $content = preg_replace('/\<[\/]{0,1}div[^\>]*\>/i', '', $content);
            $str = str_get_html($content);
            $index = $str->find("mfc-index")[0]->innertext;
            $index = str_replace('\'', '"', $index);
            $index_json = json_decode($index);

            $transition = $index_json->transition;
            $title = $index_json->title;
            $description = $index_json->description;
            $canonical = $index_json->canonical;
            $keywords = $index_json->keywords;

            $config_data = $str->find("section")[0]->innertext;
            $config_data = str_replace($str->find("mfc-index")[0]->outertext, '', $config_data);
            $config_json = json_decode($config_data);


            $versionUrl = "";
            $urlAnalytics = "";
            $urlGoogleMaps = "";
            $apiKeysGoogleMaps = "";
            $urlRecaptcha = "";
            $apiKeysRecaptcha = "";
            $language = "";
            $countryCSS = "";
            if (isset($config_json->data->version)) {
                $full_version = $config_json->data->version;
                $version_parts = explode(".", $full_version);
                $versionUrl = substr($version_parts[0], 1) . "/" . $version_parts[1] . "/" . $version_parts[2];
            }
            $app_css_name = "app";
            if (isset($config_json->data->entity)) {
                $entity = strtolower($config_json->data->entity);
                if ($entity == 'verti') {
                    $app_css_name = "app_verti";
                }
            }

            if (isset($config_json->data->urlAnalytics)) {
                $urlAnalytics = $config_json->data->urlAnalytics;
            }

            if (isset($config_json->data->urlGoogleMaps)) {
                $urlGoogleMaps = $config_json->data->urlGoogleMaps;
            }

            if (isset($config_json->data->apiKeysGoogleMaps)) {
                $apiKeysGoogleMaps = $config_json->data->apiKeysGoogleMaps;
            }

            if (isset($config_json->data->urlRecaptcha)) {
                $urlRecaptcha = $config_json->data->urlRecaptcha;
            }

            if (isset($config_json->data->full_version)) {
                $full_version = $config_json->data->full_version;
            }

            if (isset($config_json->data->language)) {
                $language = $config_json->data->language;
            }

            if (isset($config_json->data->countryCSS)) {
                $countryCSS = $config_json->data->countryCSS;
            }
            if (isset($config_json->data->urlGoogleTagManager)) {
                $urlGoogleTagManager = $config_json->data->urlGoogleTagManager;
            }
            if (isset($config_json->data->dominioCdnDes)) {
                $dominioCdnDes = $config_json->data->dominioCdnDes;
            }
            if (isset($config_json->data->dominioCdnPre)) {
                $dominioCdnPre = $config_json->data->dominioCdnPre;
            }
            if (isset($config_json->data->dominioCdnPro)) {
                $dominioCdnPro = $config_json->data->dominioCdnPro;
            }
            if (isset($config_json->data->dominioDes)) {
                $dominioDes = $config_json->data->dominioDes;
            }
            if (isset($config_json->data->dominioPre)) {
                $dominioPre = $config_json->data->dominioPre;
            }
            if (isset($config_json->data->dominioPro)) {
                $dominioPro = $config_json->data->dominioPro;
            }

            $fecha = new DateTime();
            $date = $fecha->getTimestamp();
            
            if ($_SERVER['HTTPS']) {
                $url = "https://" . $_SERVER["HTTP_HOST"];
            } else {
                $url = "http://" . $_SERVER["HTTP_HOST"];
            } 
            
            //obtener entorno
            switch ($url) {
                case $dominioDes:
                    $entorno = 'des';
                    break;
                case $dominioPre:
                    $entorno = 'pre';
                    break;
                case $dominioPro:
                    $entorno = 'pro';
                    break;
                default:
                    $entorno='default';
            }

            //obtener href
            switch ($entorno) {
                case 'des':
                    $href = $dominioCdnDes;
                    break;
                case 'pre':
                    $href = $dominioCdnPre;
                    break;
                case 'pro':
                    $href = $dominioCdnPro;
                    break;
                default:
                    $href = '../..';
            }

            $o = '<!doctype html>
<html ng-app="come" ng-strict-di>

  <head>
    <meta charset="utf-8">
    <title>' . $title . '</title>
    <meta name="description" content="' . $description . '">';

            if ($canonical != "") {
                $o .= '<link rel="canonical" href="' . $canonical . '">';
            }
            if ($keywords != "") {
                $o .= '<meta name="keywords" content="' . $keywords . '">';
            }

            $o .= '  <meta name="viewport" content="width=device-width">
    <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->
    <link rel="icon" href="favicon.ico" type="image/x-icon" />';

            if ($urlAnalytics != "" || $urlGoogleMaps != "" || $apiKeysGoogleMaps != ""
                || $urlRecaptcha != "" || $apiKeysRecaptcha != "") {
                $o .= '<script type = "text/javascript">
                var urlAnalytics = \'' . $urlAnalytics . '\';
                var urlGoogleMaps = \'' . $urlGoogleMaps . '\';
                var apiKeysGoogleMaps = \'' . $apiKeysGoogleMaps . '\';
                var mfcGoogle = {
                    "recaptcha": {
                      "url": "' . $urlRecaptcha . '",
                      "key": "' . $apiKeysRecaptcha . '"
                    }
                };
                </script>';
            }
            $o .= '
            <link rel="stylesheet" href="' . $href . '/come-round/' . $versionUrl . '/styles/' . $app_css_name . '.css">';

            /* especificCSS */
            foreach (glob(TC . 'images/' . '*.css') as $css) {
                $o .= '<link type="text/css" rel="stylesheet" href="../../' . $css . '">' . "\n";
            }
            if ($urlGoogleTagManager != ""){
                $o .= '<script type = "text/javascript">
                            var gtmId = "'. $urlGoogleTagManager .'";
                            if(gtmId != null && typeof gtmId !== "undefined"){
                                (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({"gtm.start": new Date().getTime(),event:"gtm.js"});
                                var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!="dataLayer"?"&l="+l:"";
                                j.async=true;j.src="https://www.googletagmanager.com/gtm.js?id="+i+dl;f.parentNode.insertBefore(j,f);
                                })(window,document,"script","dataLayer", gtmId);
                            }
                        </script>';
            }

            if ($urlAnalytics != ""){
                $o .= '<script type="text/javascript" src="'.$urlAnalytics.'"></script>';
            }
            $o .= '</head>
            <body>';

            if ($urlGoogleTagManager != ""){
                $o .= '<!-- TemplateBeginIf cond="urlGoogleTagManager" -->
                <!-- Google Tag Manager (noscript) -->
               
                <noscript>
                <iframe src="https://www.googletagmanager.com/ns.html?id='.$urlGoogleTagManager.'" height="0" width="0" style="display:none;visibility:hidden"></iframe>
                </noscript>
                
                <!-- End Google Tag Manager (noscript) -->';
            }

            $o .= '<div ui-view></div>

            <script src="'. $href . '/come-round/' . $versionUrl . '/scripts/vendor.js"></script>';

            if ($language != "") {
                $o .= '<script src="' . $href . '/come-round/' . $versionUrl . '/i18n/angular-locale_' . $language . '.js"></script>';
            } else {
                $o .= '<script src="'. $href .'/come-round/' . $versionUrl . '/i18n/angular-locale_es-es.js"></script>';

            }

            $o .= '<script src="' . $href . '/come-round/' . $versionUrl . '/scripts/app.js"></script>
   
            <script src="../../' . TC . 'app/pages/pages.route.js?k=' . $date . '"></script>';

            /* especificJS */
            foreach (glob(TC . 'images/' . '*.js') as $js) {
                $o .= '<script src="../../' . $js . ' "></script>' . "\n";
            }
            $o .= '</body>
</html>';
            $file_name = TC . basename($permalink) . '.html';
            file_put_contents($file_name, $o);
        } else if(basename($permalink) == "assets"){ //la página siempre debe tener slug "assets"
            //Css
            foreach ($str->find("mfc-css") as $css) {

                $name = $css->attr['name'];
                $code = $css->attr['css'];

                $code = str_replace('<style>', '', $code);
                $code = str_replace('</style>', '', $code);
                $code = str_replace('\"', '"', $code);

                $css_file_name = TC . 'images/' . $name . '.css';

                file_put_contents($css_file_name, $code);
                echo "Se ha generado el archivo " . $css_file_name;
                echo "<br>";
            }
            //Js
            foreach ($str->find("mfc-js") as $js) {

                $name = $js->attr['name'];
                $code = $js->attr['js'];

                $code = str_replace('<script>', '', $code);
                $code = str_replace('</script>', '', $code);
                $code = str_replace('\"', '"', $code);

                $js_file_name = TC . 'images/' . $name . '.js';

                file_put_contents($js_file_name, $code);
                echo "Se ha generado el archivo " . $js_file_name;
                echo "<br>";
            }

        } else if (basename($permalink) == "mocks") { //la página siempre debe tener slug "mocks"
            foreach ($str->find("mfc-mock") as $mock) {

                $name = $mock->attr['name'];
                $code = $mock->attr['json'];

                $code = str_replace("'", '"', $code);

                $mock_file_name = TC . 'app/pages/mocks/' . $name . '.json';

                file_put_contents($mock_file_name, $code);
                echo "Se ha generado el archivo " . $mock_file_name;
                echo "<br>";
            }
        } else {
            /*** AJUSTAR MFC-GROUP-CONDITION Y MFC-SHADOW-BOX ***/
            $str = str_get_html($content);
            if (count($str->find('mfc-group-condition')) > 0 || count($str->find('mfc-shadow-box')) > 0) {
                if (count($str->find('mfc-group-condition')) > 0) {
                    $close = '</mfc-group-condition>';
                } else {
                    $close = '</mfc-shadow-box>';
                }
                foreach ($str->find("section div") as $i => $element) {

                    $data = $element->children;
                    $new_text = "";
                    $name_condition = [];
                    $close_condition = [];
                    foreach ($data as $item) {
                        $att_json = json_decode(str_replace("'", "\"", $item->attr['mfc-properties']));
                        $parent_name = $att_json->parentComp;
                        $i = 0;
                        while ($i < count($name_condition) && $parent_name != end($name_condition)) {
                            $new_text .= end($close_condition);
                            array_pop($name_condition);
                            array_pop($close_condition);
                        }
                        if ($item->tag == "mfc-group-condition" || $item->tag == "mfc-shadow-box") {
                            if ($item->tag == "mfc-group-condition") {
                                $close = '</mfc-group-condition>';
                            } else {
                                $close = '</mfc-shadow-box>';
                            }
                            $group_name = $att_json->name;
                            if ($item->tag == "mfc-shadow-box") {
                                if ($att_json->show) {
                                    $show = 'true';
                                } else {
                                    $show = 'false';
                                }
                                $new_text .= '<' . $item->tag . ' show="' . $show . '" mfc-properties="' . $item->attr['mfc-properties'] . '">';
                            } else {
                                $new_text .= '<' . $item->tag . ' mfc-properties="' . $item->attr['mfc-properties'] . '">';
                            }
                            array_push($name_condition, $group_name);
                            array_push($close_condition, $close);
                        } else {
                            $new_text .= '<' . $item->tag . ' mfc-properties="' . $item->attr['mfc-properties'] . '"></' . $item->tag . '>';
                        }
                    }

                    for ($i = 0; $i < count($name_condition); $i++) {
                        $new_text .= end($close_condition);
                        array_pop($close_condition);
                    }

                    $content = str_replace($element->innertext, $new_text, $content);
                }
            }
            /*** FIN AJUSTAR MFC-GROUP-CONDITION / MFC-SHADOW-BOX***/

            // Quitamos clases innecesarias de divi         \s*+ -> 0 o + espacios
            $content = preg_replace('/\s*+et_pb_row(_[0-9]+)*\s*+/', '', $content);

            //Cuando sea una sola fila, eliminar el <div>
            if (sizeof($str->find('mfc-layout div.et_pb_row')) == 1) {
                $content = str_replace('<section class="mfc-layout__main-container__fullwidth"><div class="mfc-grid-12">', '<section class="mfc-layout__main-container__fullwidth">', $content);
                $content = str_replace('</div> </section>', '</section>', $content);
            }

            if ($file_name == "") {
                //Nombre del fichero:
                $file_name = TC . 'app/pages/' . basename($permalink) . '.html';
                // $content = str_replace('><', '>'."\n".'<', $content);
                file_put_contents($file_name, $content);
            }    
        }

        //Header
        if (strpos(basename($permalink),'header') !== false ){
            $trimHeaderName = preg_replace("/\s+/", "", HEADER_NAME);
            $file_name = TC . 'app/pages/' . $trimHeaderName . '.html';
            $content = str_replace('<mfc-layout>', '', $content);
            $content = str_replace('</mfc-layout>', '', $content);
            preg_match_all('/\<section(.*?)\>(.*?)\<\/section\>/s', $content, $matches );
            $content = str_replace($matches[1][0], '', $content);
            preg_match_all('/\<div(.*?)\>(.*?)\<\/div\>/s', $content, $matches );
            $content = str_replace($matches[1][0], '', $content);
            $content = str_replace('<section>', '', $content);
            $content = str_replace('</section>', '', $content);
            $content = str_replace('<div>', '', $content);
            $content = str_replace('</div>', '', $content);
            file_put_contents($file_name, $content);
        }

        //Footer
        if (strpos(basename($permalink),'footer') !== false ){
            $trimFooterName = preg_replace("/\s+/", "", FOOTER_NAME);
            $file_name = TC . 'app/pages/' . $trimFooterName . '.html';
            $content = str_replace('<mfc-layout>', '', $content);
            $content = str_replace('</mfc-layout>', '', $content);
            preg_match_all('/\<section(.*?)\>(.*?)\<\/section\>/s', $content, $matches );
            $content = str_replace($matches[1][0], '', $content);
            preg_match_all('/\<div(.*?)\>(.*?)\<\/div\>/s', $content, $matches );
            $content = str_replace($matches[1][0], '', $content);
            $content = str_replace('<section>', '', $content);
            $content = str_replace('</section>', '', $content);
            $content = str_replace('<div>', '', $content);
            $content = str_replace('</div>', '', $content);
            file_put_contents($file_name, $content);
        }

        echo "PÁGINA: " . $file_name . " Creada correctamente!! ";

    } else {
        echo "\n" . "OJO!!! NO SE HA PODIDO RECUPERAR SU CONTENIDO!!!" . "\n";
    }

}

function removedLinebaksAndWhitespace($content)
{
    $pattern = '/\s*/m';
    $replace = '';
    return preg_replace($pattern, $replace, $content);
}

function array_filter_recursive($input)
{
    foreach ($input as &$value) {
        if (is_array($value)) {
            $value = array_filter_recursive($value);
        }
    }

    return array_filter($input, 'myFilter');
}

function myFilter($var)
{
    return ($var !== NULL && $var !== '' && $var !== array());
}

function addCustomClass($content, $search1, $search2, $num)
{
    $str = str_get_html($content);
    foreach ($str->find('mfc-layout div.et_pb_column_' . $search1 . '_' . $search2) as $i => $element) {
        $a = $element->outertext;
        $b = $element->innertext;
        if ($search1 == "4" && $search2 == "4") {
            $no_divs = preg_replace('/\<[\/]{0,1}div[^\>]*\>/i', '', $a);
            $content = str_replace($a, $no_divs, $content);
        } else {
            $no_divs = preg_replace('/\<[\/]{0,1}div[^\>]*\>/i', '', $b);
            $first = '';

            if (strpos($a, "first_column ") != false) {
                $first = 'mfc-first-row-column ';
            }
            $mfc_offset = '';
            if (strpos($a, "has_offset_") != false) {
                preg_match_all('/has_offset_([^"]+)/', $a, $m);
                if ($m) {
                    $off_num = explode(' ', $m[1][0])[0];
                }
                $num_off = getOffset($off_num);
                $mfc_offset = 'mfc-col-offset-' . $num_off . ' ';
            }
            if (strpos($no_divs, "'classname':''") != false) {
                $class = $first . $mfc_offset . 'mfc-col-' . $num;
            } else {
                $class = $first . $mfc_offset . 'mfc-col-' . $num . ' ';
            }

            $add_class = str_replace("'classname':'", "'classname':'" . $class, $no_divs);
            $content = str_replace($a, $add_class, $content);
        }

    }
    return $content;
}

function getOffset($off_num)
{
    switch ($off_num) {
        case "1_2":
            $offset = "6";
            break;
        case "1_3":
            $offset = "4";
            break;
        case "1_4":
            $offset = "3";
            break;
        case "1_5":
            $offset = "1";
            break;
        case "1_6":
            $offset = "2";
            break;
        case "2_5":
            $offset = "2";
            break;
        case "3_5":
            $offset = "3";
            break;
        case "3_4":
            $offset = "9";
            break;
    }
    return $offset;
}

function copyRecursively($fuente, $destino)
{
    if(is_dir($fuente))
    {
        $dir=opendir($fuente);
        while($archivo=readdir($dir))
        {
            if($archivo!="." && $archivo!="..")
            {
                if(is_dir($fuente."/".$archivo))
                {
                    if(!is_dir($destino."/".$archivo))
                    {
                        mkdir($destino."/".$archivo);
                    }
                    copyRecursively($fuente."/".$archivo, $destino."/".$archivo);
                }
                else
                {
                    copy($fuente."/".$archivo, $destino."/".$archivo);
                    $dt = filemtime($fuente."/".$archivo);
                    if ($dt) {
                        touch($destino."/".$archivo, $dt);
                    }
                }
            }
        }
        closedir($dir);
    }
    else
    {
        copy($fuente, $destino);
        $dt = filemtime($fuente);
        if ($dt) {
            touch($destino, $dt);
        }
    }
}

function curl_get_contents($url)
{
    $ch = curl_init($url);
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    
    $data = curl_exec($ch);
    curl_close($ch);

    $html_base = new simple_html_dom();
    // Load HTML from a string
    $html_base->load($data);
    
    return $html_base;
}

?>