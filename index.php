<?php
include("simple_html_dom.php");
ini_set('user_agent', 'My-Application/2.5');

$html = file_get_html('https://www.kempstoncontrols.co.uk/Manufacturers');
// Find all links
// echo $html->find('a',0)->plaintext;
// Find all links
$count = 0;
$filename = 'productData.csv';
header('Content-type: text/csv');
header("Content-Disposition: attachment; filename=$filename");
$output = fopen('file.csv', "w");
$excel_array = [];
foreach ($html->find('.row .col-md-3 a') as $element) {
    $count++;
    if ($count > 4) {

        $url = "";
        $product_url_arr = explode("/", $element->href);

        if (strpos($element->href, 'page') !== false) {
            $url = 'https://www.kempstoncontrols.co.uk' . $element->href;
        } else {
            $url = 'https://www.kempstoncontrols.co.uk' . $element->href . '/page/1';
        }
        $html2 = file_get_html($url);
        if ($html2->find('.pagination span', 0)) {

            $page = $html2->find('.pagination span', 0)->plaintext;

            $span = str_replace(" ", "", $page);
            $arr = explode("of", $span);
        } else {
            $arr[0] = 0;
            $arr[1] = 1;
        }
        $product_index = 0;

        for ($index = 0; $index <= $arr[1]; $index++) {

            $new_url = str_replace("1", "", $url);

            $html3 = file_get_html($new_url . $index);
            $products = [];
            $data_count_stock = count($html3->find('#manufacturers-products .product-grid-price'));
            if ($data_count_stock > 0) {
                for ($index_1 = 0; $index_1 < $data_count_stock; $index_1++) {
                    if ($html3->find('#manufacturers-products .product-grid-price .stock', $index_1)) {
                        $stock_data = $html3->find('#manufacturers-products .product-grid-price .stock', $index_1)->plaintext;
                        $stock_data_arr = explode(" ", $stock_data);
                        if ($stock_data_arr[0] == "Available") {
                            $stock_qty = implode(" ", $stock_data_arr);
                        } else {
                            $stock_qty = $stock_data_arr[0];
                        }
                        $products[$index_1]['stock'] = $stock_qty;
                        $products[$index_1]['products'] = $product_url_arr[2];
                    } else {
                        $products[$index_1]['stock'] = 0;
                        $products[$index_1]['products'] = $product_url_arr[2];
                    }
                }

                $data_count_details = count($html3->find('#manufacturers-products .product-details p a'));
                $product_details_index = 0;
                for ($index_2 = 0; $index_2 < $data_count_details; $index_2++) {
                    $stock_data_details = $html3->find('#manufacturers-products .product-details p a', $index_2)->plaintext;
                    if ($stock_data_details != "Check Availability" && strpos($stock_data_details, "days") == false) {

                        $products[$product_details_index]['parts_details'] = $stock_data_details;
                        $product_details_index++;
                    }
                }

                $data_count_part = count($html3->find('#manufacturers-products .product-details h2 a'));
                for ($index_3 = 0; $index_3 < $data_count_part; $index_3++) {
                    $stock_data_part = $html3->find('#manufacturers-products .product-details h2 a', $index_3)->plaintext;
                    $products[$index_3]['parts_number'] = $stock_data_part;
                }
                if ($index == 1) {

                    $header = array_keys($products[0]);
                    fputcsv($output, $header);
                }

                foreach ($products as $row) {
                    print_r($row);
                    fputcsv($output, $row);
                }
            }
        } //page iteration loop ends
    } //brands if ends

}
echo "it's done!";
fclose($output);
exit;
