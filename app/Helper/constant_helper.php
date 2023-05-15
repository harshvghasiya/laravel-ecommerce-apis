<?php

// Show alert Messages
function flashMessage($type, $message)
{
    \Session::put($type, $message);

}

// for Status Icon and Data in yajara box
function getStatusIcon($data)
{
    if ($data->status == 1) {
        return '<span title="' . trans('message.click_on_button_change_status_label') . '" class="btn btn-sm btn-success" id="active_inactive"
        status="1" data-id="' . \Crypt::encryptString($data->id . timeFormatString()) . '">' . trans('message.active') . '</span>';
    } else {
        return '<span title="' . trans('message.click_on_button_change_status_label') . '" class="btn btn-sm btn-danger" id="active_inactive"
        status="0" data-id="' . \Crypt::encryptString($data->id . timeFormatString()) . '">' . trans('message.inactive') . '</span>';
    }
}

// Upload And Download Server Url
function uploadAndDownloadUrl()
{
    return asset('');
}

// Upload and download path
function uploadAndDownloadPath()
{
    return public_path();
}

// Company Routes Prefix Keyword
function companyPrefixKeyword()
{
    return "company";
}

//  Company Routes Name
function companyRouteName()
{
    return 'company.';
}

// Common Route Prefix Keyword
function routePrefixKeyword()
{
    return "admin";
}

// Common Route Name
function routeRouteName()
{
    return "admin.";
}

// Basic Setting prefix keyword
function basicSettingPrefixKeyword()
{
    return 'basic-settings';
}

// Basic setting route prefix keyword
function basicSettingRouteName()
{
    return 'basic_setting.';
}

// Video  Route prefix
function videoPrefixKeyword()
{
    return 'video';
}

// Video Route name
function videoRouteName()
{
    return 'video.';
}

// Upload  Images
function uploadFile($r, $name, $uploadPath)
{

    $image = $r->$name;
    $name = time() . '' . $image->getClientOriginalName();

    $image->move($uploadPath, time() . '' . $image->getClientOriginalName());

    return $name;
}

// Favicon Upload path
function faviconImageUploadPath()
{
    return uploadAndDownloadPath() . '/upload/basic_setting/favicon/';
}

// Video Upload path
function videoUploadPath($company_name)
{
    return uploadAndDownloadPath() . '/upload/videos/' . $company_name . '/';
}

// Product Upload path
function productUploadUrl()
{
    return uploadAndDownloadUrl() . '/upload/product/';
}

// Video thumbnail Upload path
function thumbnailUploadPath($company_name)
{
    return uploadAndDownloadPath() . '/upload/videos/' . $company_name . '/thumb/';
}

// Logo Upload path
function logoImageUploadPath()
{
    return uploadAndDownloadPath() . '/upload/basic_setting/logo/';
}

// Product Upload path
function productUploadPath()
{
    return uploadAndDownloadPath() . '/upload/product/';
}

// Upload and download url
function faviconImageUploadUrl()
{

    return uploadAndDownloadUrl() . 'upload/basic_setting/favicon/';
}

// Video  download url
function videoUploadUrl($company_name)
{

    return uploadAndDownloadUrl() . 'upload/videos/' . $company_name . '/';
}

// Video thumbnail download url
function thumbnailUploadUrl($company_name)
{

    return uploadAndDownloadUrl() . 'upload/videos/' . $company_name . '/thumb/';
}

// Upload and download url
function logoImageUploadUrl()
{

    return uploadAndDownloadUrl() . 'upload/basic_setting/logo/';
}

// If Image not avalaible this image will show
function noImageUrl()
{
    return uploadAndDownloadUrl() . 'asset/no_image.png';
}

// GET Basic setting data
function getBasicSetting()
{
    $redis = Redis::connection();
    $setting = Redis::get('basic_setting');

    if (isset($setting) && $setting != null) {
        $setting = json_decode($setting);
        return $setting;
    } else {
        $data = \App\Models\BasicSetting::select('id', 'logo', 'favicon', 'is_recaptcha', 'website_title', 'google_analytics_script', 'is_recaptcha', 'google_recaptcha_site_key', 'google_recaptcha_secret_key', 'is_analytics')->first();
        $data->logo = $data->getLogoImageUrl();
        $data->favicon = $data->getFaviconImageUrl();
        Redis::set('basic_setting', $data);
        return $data;
    }
}

// Email Template prefix
function emailTemplatePrefixKeyword()
{
    return 'email-template';
}

// EMail Template route name
function emailTemplateRouteName()
{
    return 'email_template.';
}

// Role route prefix
function rolePrefixKeyword()
{
    return 'role';
}

// Role route name
function roleRouteName()
{
    return 'role.';
}

// Genrate Token
function generateToken()
{
    $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
    $token = '';
    for ($i = 0; $i < 6; $i++) {
        $token .= $characters[rand(0, strlen($characters) - 1)];
    }
    $token = time() . $token . time();
    return $token;
}

// Send Mail
function sendMail($user, $email_body, $data)
{
    $mail_config = \App\Models\BasicSetting::select('id', 'is_smtp', 'from_mail', 'to_mail', 'smtp_host', 'smtp_port', 'encryption', 'smtp_username', 'smtp_password', 'smtp_status')->first();
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSendmail();
    $mail->Host = $mail_config->smtp_host;
    $mail->SMTPAuth = true;
    $mail->Username = $mail_config->smtp_username;
    $mail->Password = $mail_config->smtp_password;
    $mail->SMTPSecure = $mail_config->encryption;
    $mail->Port = $mail_config->smtp_port;

    //Recipients
    $mail->setFrom($mail_config->from_mail);

    $mail->addAddress($user->email, trans('message.company_registration_otp'));

    // Content
    $mail->isHTML(true);
    $mail->Subject = $data->subject;
    $mail->Body = view('admin.emails.mail_template', compact('email_body'));
    $mail->send();
    if (!$mail->send()) {
        return false;
    } else {
        return true;
    }
}

// Country List
function getCountryList($old_data = '')
{
    $data = \App\Models\Country::where('status', \App\Models\Country::STATUS_ACTIVE)->pluck('name', 'name')->toArray();
    return $data;
}

// Admin Authorization
function authorize($user, $slug)
{
    $permission = \App\Models\Permission::select('id', 'module_id', 'name', 'slug')->with('permission_role')->where('slug', $slug)->first();
    if ($permission == null) {
        return false;
    }
    if (in_array($user->role_id, $permission->permission_role->pluck('role_id')->toArray()) || $user->id == \App\Models\Role::SUPER_ADMIN) {
        return true;
    }
    return false;
}

// Get Company name
function companyName($video_id = "")
{
    if ($video_id != null) {
        $company_name = $video_id->video_user->name;
    } else {
        $company_name = \Str::slug(\Auth::user()->name);
    }

    return $company_name;
}

//Admin company name
function adminCompanyName($user_id)
{
    $res = \App\Models\User::select('id', 'name')->where('id', $user_id)->first();
    if ($res != null) {
        $company_name = $res->name;
    } else {
        $company_name = \Auth::user()->id;
    }

    return $company_name;
}

// To get decrypted id
function getDecryptedId($id)
{
    $str = \Crypt::decryptString($id);
    $deid = 0;

    if ($str != null) {
        $str = explode('%sss%', $str);
        $deid = $str[0];
        return $deid;
    } else {

        try {

            $deid = \Crypt::decryptString($id);
            return $id;

        } catch (\Exception $e) {

            return $deid;
        }
    }

    return $deid;
}

// Time Format String
function timeFormatString()
{
    $time = "%sss%" . now()->format('Y-m-d H:i:s');
    return $time;
}

// get Default timezone
function getTimeZone()
{
    return 'UTC';
}
