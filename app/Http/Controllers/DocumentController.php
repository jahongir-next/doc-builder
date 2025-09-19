<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DocumentController extends Controller
{

    public function generate()
    {
        $content = 'https://www.youtube.com';
        $qrcode = $this->generateQrCode($content);

        $template = Storage::disk('do_not_delete')->get('sample.docbuilder');
        $fileName = "certificate" . '.docx';
        $filePath = storage_path('app/documents/'.$fileName);
        if (Storage::exists($filePath)) {
            Storage::delete($filePath);
        }
        $sourcePath = storage_path('app/do_not_delete/sample.docx');

        $content = strtr($template , self::safeTextArray( [
            '{{filePath}}' => $filePath,
            '{{sourcePath}}' => $sourcePath,
            '{{name}}' => "Ёнғин хавфсизлиги бошқармаси, Тошкент ш М.Улуғбек кўчаси",
            '{{cert_number}}' => "C-156415848",
            '{{given_date}}' => "15.09.2025",
            '{{expire_date}}' => "14.09.2026",
            '{{org_name}}' => "OOO GarantStroy",
            '{{address}}' => "Тошкент шахар Яшнобод тумани 25 уй 2 хонадон",
            '{{address2}}' => "Тошкент шахар Чилонзор тумани 25 уй 2 хонадон",
            '{{standart}}' => "O’Z DSt ISO 9001:2015, O’Z DSt ISO 14001:2015, O’Z DSt ISO 45001:2015",
            '{{type}}' => "Қурилиш махсулотларини ишлаб чиқариш",
            '{{qrcode}}' => $qrcode,
            '{{fullname}}' => "Jumaniyozov J E",
        ]));

        $random_string = Str::random();
        $temporary_builder_file_path = storage_path("app/temp") . '/' . date('Y_m_d_H_i_s_') . '_add_exp_data_' . $random_string . '.docbuilder';
        return self::execScript($temporary_builder_file_path, $content);
    }

    private static function execScript(string $builder_file_path, $content): bool
    {
        if (file_put_contents($builder_file_path, $content)) {
            if(config('app.env') == 'local'){
                exec("\"C:\Program Files\ONLYOFFICE\DocumentBuilder\docbuilder\" " . " $builder_file_path 2>&1", $output);
            }
            else{
                exec(config('only-office.builder') . " $builder_file_path 2>&1", $output);
            }
            if (count($output) !== 0) {
                return false;
            }
            if (file_exists($builder_file_path)) {
                info('execScript', [$builder_file_path]);
                return unlink($builder_file_path);
            }
        } else {
            echo "file_put_contents($builder_file_path, ...) error\n";
            return false;
        }
        return true;

    }




    public static function safeTextArray(array $texts): array
    {
        $result = [];
        foreach ($texts as $key => $text)
            $result[$key] = self::safeText($text??'');
        return $result;
    }

    public static function safeText(string $text = ''): string
    {

        $text = str_replace(["\n", "\t", "\r"], ' ', $text);
        $text = str_replace(["‘", "ʻ", "ʼ", "’", "\"", "`"], "'", $text);
        return trim($text);
    }

    public function generateQrCode($data)
    {
        $qrCode = base64_encode(QrCode::encoding('UTF-8')->format('png')->size(100)->generate($data));
        return 'data:image/png;base64,' . $qrCode;
    }
}
