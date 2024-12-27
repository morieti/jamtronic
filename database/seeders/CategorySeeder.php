<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $f = fopen(base_path() . '/database/seeders/categories.csv', 'r');
        fgetcsv($f);
        while (!feof($f)) {
            $row = fgetcsv($f);
            if (!$row) {
                continue;
            }

            try {
                $url = unserialize($row[6])['url'];
                $content = file_get_contents($url);
                $fileName = explode('/', $url);
                $fileName = end($fileName);

                $tmpPath = '/tmp/' . $fileName;
                file_put_contents($tmpPath, $content);

                $file = new UploadedFile($tmpPath, $fileName);
                $imagePath = $file->store('category_images', 'public');
                $imagePath = 'storage/' . $imagePath;
            } catch (\Throwable $exception) {
                $imagePath = null;
            }

            try {
                Category::create([
                    'id' => $row[0],
                    'name' => $row[1],
                    'slug' => urldecode($row[2]),
                    'description' => $row[3],
                    'parent_id' => $row[4] == 0 ? null : $row[4],
                    'image' => $imagePath,
                ]);
            } catch (\Throwable $exception) {
                dd($exception->getMessage(), $exception->getLine(), $row);
            }
        }

    }
}
