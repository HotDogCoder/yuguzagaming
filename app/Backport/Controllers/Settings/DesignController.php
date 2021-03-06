<?php

namespace App\Backport\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Wiledia\Backport\Layout\Content;
use Wiledia\Backport\Settings;

class DesignController extends Controller
{
    /**
     * Index interface.
     *
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('Design')
            ->body($this->form()->render());
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    public function form()
    {
        $settingModel = 'Wiledia\Backport\Settings\Setting';

        $settings = new Settings(new $settingModel());

        $settings_model = \Wiledia\Backport\Settings\Setting::where('category', 'design')->orderBy('reorder')->get();

        foreach ($settings_model as $setting) {
            if (isset($setting->field['hint'])) {
                $settings->__call($setting->field['type'], [$setting->key, $setting->name])->help($setting->field['hint'])->value($setting->value);
            } else {
                $settings->__call($setting->field['type'], [$setting->key, $setting->name])->value($setting->value);
            }
        }

        return $settings;
    }

    /**
     * Update the specified resource in Storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $form = $this->form()->update('design', $request->all());

        $settings = \Wiledia\Backport\Settings\Setting::where('category', 'design')->orderBy('reorder')->get();

        foreach ($settings as $setting) {
            if ($request->file($setting->key)) {
                // *
                // *
                // Logo Upload
                // *
                // *
                if ($setting->key === 'logo') {
                    $disk = 'img';
                    // Make the image
                    $image = \Image::make($request->file('logo'));

                    // Store retina
                    if ($image->height() >= 80) {
                        $image->resize(null, 80, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                    }

                    // Retina filename.
                    $filename_retina = 'logo@2x.png';
                    // Store the retina image on disk.
                    Storage::disk($disk)->delete($filename_retina);
                    Storage::disk($disk)->put($filename_retina, $image->stream());

                    // Store default logo
                    if ($image->height() >= 40) {
                        $image->resize(null, 40, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                    }

                    // default filename.
                    $filename_default = 'logo.png';
                    // Store the default image on disk.
                    Storage::disk($disk)->delete($filename_default);
                    Storage::disk($disk)->put($filename_default, $image->stream());

                    $setting->value = 'img/logo.png';
                    $setting->save();
                // *
                // *
                // Favicon Upload
                // *
                // *
                } elseif ($setting->key === 'favicon') {
                    $disk = 'img';
                    // Make the image
                    $image = \Image::make($request->file('favicon')); //

                    // Store 32x32
                    $image->resize(32, 32);

                    // Retina filename.
                    $filename_32 = 'favicon-32x32.png';
                    // Store the retina image on disk.
                    Storage::disk($disk)->delete($filename_32);
                    Storage::disk($disk)->put($filename_32, $image->stream());

                    // Store 16x16 logo
                    $image->resize(16, 16);

                    // default filename.
                    $filename_16 = 'favicon-16x16.png';
                    // Store the default image on disk.
                    Storage::disk($disk)->delete($filename_16);
                    Storage::disk($disk)->put($filename_16, $image->stream());

                    $setting->value = 'img/favicon-32x32.png';
                    $setting->save();
                // *
                // *
                // Landing page image upload
                // *
                // *
                } elseif ($setting->key === 'landing_image') {
                    $disk = 'img';
                    // Make the image
                    $image = \Image::make($request->file('landing_image'));

                    // Set JPG Quality of the image
                    $image->encode('jpg', config('settings.jpeg_quality'));

                    // Landing page filename.
                    $filename = 'landing.jpg';
                    // Store the retina image on disk.
                    Storage::disk($disk)->delete($filename);
                    Storage::disk($disk)->put($filename, $image->stream());
                    $setting->value = 'img/landing.jpg';
                    $setting->save();
                }
            }
        }

        return $form;
    }

    /**
     * Store a newly created resource in Storage.
     *
     * @return mixed
     */
    public function store()
    {
        return $this->form()->store();
    }

    /**
     * Remove the specified resource from Storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if ($this->form()->destroy($id)) {
            $data = [
                'status'  => true,
                'message' => trans('admin.delete_succeeded'),
            ];
        } else {
            $data = [
                'status'  => false,
                'message' => trans('admin.delete_failed'),
            ];
        }

        return response()->json($data);
    }
}
