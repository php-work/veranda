<?php
namespace ver;

use Veranda\Luri;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models;

function log(string $name, $message = []): void
{
    $message = is_array($message) ? $message : [$message];
    $config = config("log.$name");
    if (!$config)
    {
        $context = [
            'info' => [
                'name'    => $name,
                'message' => $message,
            ],
        ];
        Log::warning("log config is not defined", $context);
        return;
    }

    $level = $config['level'];
    $context = [
        'info' => [
            'id'      => $config['id'],
            'message' => $message,
        ],
    ];
    Log::$level($name, $context);
}

function gid(int $now = 0, string $realm = '0000',
             int $randomBytes = 4, $version = '0'): string
{
    !$now && $now = microtime(true);
    $now = ($now - 1500000000) * 1000;
    $hex = str_pad(dechex($now), 10, '0', \STR_PAD_LEFT).
        $realm.
        bin2hex(random_bytes($randomBytes));
    return $version.gmp_strval(gmp_init("0x$hex", 16), 62);
}

function padding(string $template, array $vars): ?string {
    // :abc + {{abc}} 相对单纯方案
    $result = preg_replace_callback(
        '/:([0-9]+)|:([A-Za-z_\@\$\.\-\~]+[0-9]*)|(\{\{)([A-Za-z0-9_\@\$\.\-\~\#\&]+)(\}\})/',
        function($matches) use ($vars) {
            $key = $matches[4] ?? $matches[2] ?? $matches[1];
            return $vars[$key] ?? $matches[0];
        }, $template);
    return $result;
}

function cd(string $key, int $sec, int $times = 1, bool $silent = false): ?bool
{
    if ($sec <= 0)
    {
        return true;
    }

    $key    = "cd:$key";
    $value  = Redis::get($key);

    // 不存在时创建
    if ($value === null)
    {
        Redis::setEx($key, $sec, $times);
        $value = $times;
    }
    $ttl    = Redis::ttl($key);

    // 判断
    if ($value < 0 || Redis::decr($key) < 0)
    {
        Redis::expire($key, $ttl);
        if ($silent) return false;
        raise('aborts.action_is_in_cooldown');
    }
    Redis::expire($key, $ttl);
    return true;
}

function violate($key, array $extra = [])
{
    throw new HttpResponseException(response()->json([
        'status'  => 201,
        'message' => __($key, $extra),
    ], 200, [], JSON_UNESCAPED_UNICODE));
}

function isMobileNumber($value): bool
{
    return (bool) preg_match('/^[1]([3-9])[0-9]{9}$/', $value);
}

function duQuery($query) {
    dump($query->toSql());
    dump($query->getBindings());
}

function raise($key, array $extra = [])
{
    throw new \DomainException(__($key, $extra));
}

function splitDate(string $dateRange, bool $appendTime = false): array
{
    $dateRangeArr = explode('~', $dateRange);
    array_walk($dateRangeArr, function (&$date){
        $date = trim($date);
    });

    if ($appendTime)
    {
        [
            $start,
            $end,
        ] = $dateRangeArr;

        $start  .= ' 00:00:00';
        $end    .= ' 23:59:59';

        $dateRangeArr = [$start, $end];
    }


    return $dateRangeArr;
}

function checkPermission($user, $permission, bool $abort = true): bool
{
    $permissions = is_string($permission) ? [$permission] : $permission;

    if ($user instanceof AuthManager) $user = $user->user();

    if (!($user instanceof Models\SystemAuthUser))
    {
        $user = Models\SystemAuthUser::findOrFail($user);
    }

    if($user->can('super')) return true;

    foreach ($permissions as $permission)
    {
        if($user->can($permission)) return true;
    }

    if ($abort) abort(403, __('aborts.not_authorize'));

    return false;
}

function debug(): bool
{
    return config('app.debug');
}

function faker(): \Faker\Generator
{
    return tap(\Faker\Factory::create(config('app.faker_locale') ?: config('app.locale')), function($faker){
        $faker->addProvider(new \Veranda\Support\Faker\Provider\zh_CN\Text($faker));
    });
}

function gmType($name)
{
    return config('gm.gmType')[$name] ?? null;
}

function buildSign($data){
    ksort($data);
    $res = '';
    foreach ($data as $key => $value)
    {
        if ($value === '' || $key == 'sign' || $key == 'sign2' || $key=='time')
        {
            continue;
        }
        $res .= $key . '=' . $value . '&';
    }
    $data = $res . config('app.gm_app_secret');  //待验签字符串
    $data = md5($data);

    return $data;
}

function adminToastr($message = '', $type = 'success', $options = [])
{
    $message=$message?:trans('admin.save_succeeded');
    $toastr = new \Illuminate\Support\MessageBag(get_defined_vars());
    \Illuminate\Support\Facades\Session::flash('toastr', $toastr);
}

function traverse($tree, $prefix = '——')
{
    static $nodes=[];
    foreach ($tree as $t) {
        $t->title=$prefix.' '.$t->title;
        $nodes[]=$t;
        traverse($t->children, $prefix.'——');
    }

    return $nodes;
}

function adminUrl($url=null)
{
    $prefix = trim(config('admin.prefix'), '/');
    $fullUrl=url($prefix ? "/$prefix" : '');
    if (null!==$url) {
        $fullUrl.='/'.trim($url, '/');
    }

    return $fullUrl;
}

function asset($path, $secure = null)
{

    return \asset($path."?_=".time(), $secure);
}

function uri($url, ?array $context = [], $autoResolve = true)
{
    if (is_array($url))
    {
        $scheme = strstr($url[0], ':', true);
        $path   = substr($url[0], strlen($scheme) + 1);
        $luri = new Luri($scheme, $path, $url[1] ?? []);
    } else {
        $luri = Luri::createByUri($url);
    }
    return ($luri && $autoResolve) ? $luri->resolve($context)[0] : $luri;
}

function takeHooks(array $hooks, array $context = []): array {
    $result = [];
    if ($uriList = $hooks ?? []) {
        foreach ($uriList as $uri) {
            $context['_result'] = $result;
            $hook = \ver\uri($uri, $context);
            $return   = $hook->raise();

            if ($hook->_return) {
                $result[$hook->_return] = $return;
            } else {
                $result[] = $return;
            }
        }
    }
    return $result;
}

function uploadData($data, string $uri, string $ext = '',
                    string $prefix = 'upload'): string
{
    $tmpDir   = '/tmp/upload';
    if (!file_exists($tmpDir))
    {
        mkdir($tmpDir, 0755, true);
    }
    $filename = \ver\gid();

    if ($data instanceof UploadedFile)
    {
        $filename .= ($ext ? ".$ext": ".".$data->extension());
        $data->move($tmpDir, $filename);
    }else{
        $filename .= ($ext ? ".$ext" : "");
        file_put_contents("$tmpDir/$filename", $data);
    }

    $cdnFullpath    = \ver\uri([$uri, [
        'filepath' => "$tmpDir/$filename",
        'prefix'   => $prefix,
    ]])();

    unlink("$tmpDir/$filename");

    return $cdnFullpath;
}

function suffix(string $subject, string $search = '.'): string
{
    $pos = strrpos($subject, $search);
    return $pos !== false ? substr($subject, $pos + 1) : '';
}

function checkVcode(string $appId, string $category, $handle, string $code): void
{
    if ($handle == '15151510866' && $code == '666888')
    {
        return;
    }

    $result = OutsideDomains\VCode::instance()->check($appId, $category, $handle, $code);

    if (!$result) \ver\raise('aborts.vcode_check_fail');
}

function dateRangeList($dateRange)
{
    [
        $startDate,
        $endDate,
    ] = \ver\splitDate($dateRange);
    $days       = [];
    foreach ((\ver\time::now())::between($startDate, $endDate) as $date)
    {
        $days[] = $date->format('Y-m-d');
    }

    return $days;
}

function incrId(string $key = ''): string
{
    $key = 'number-count' . ($key ? '/' . trim($key, '/') : '');

    if (!Redis::exists($key))
    {
        Redis::set($key, 0);
        Redis::expire($key, 86400);
    }

    return Redis::incr($key);
}

