<?php
namespace Veranda\Foundation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Veranda\Abort;
use Illuminate\Http\Response;

trait ExceptionHandlerTrait
{

    protected function renderException($request, \Throwable $exception): ?Response
    {
        $isIgnore = function(\Throwable $exception): bool {
            if ($exception instanceof AuthenticationException) {
                return true;
            } elseif ($exception instanceof ModelNotFoundException) {
                return true;
            }
            return false;
        };
        try {
            if (!($exception instanceof Abort)) {
                // TODO: 这里的逻辑之后优化
                if ($exception instanceof \InvalidArgumentException) {
                    $abort = new Abort($exception);
                } elseif ($isIgnore($exception)) {
                    return null;
                } elseif (\ver\debug()) {
                    return null;
                } else {
                    // 在非debug模式下会将其他报错转义为一个默认报错
                    $abort = \ver\raise('server_error', [], $exception);
                }
            } else {
                // 非调试模式下致命错误将被转换为默认报错
                if ($exception->level() == 'critical'
                    && !\ver\debug())
                {
                    $abort = \ver\raise('server_error', [], $exception);
                } else {
                    $abort = $exception;
                }
            }
            return \ver\protocol::abort($abort)->send(...$abort->httpStatus());
        } catch (\Error $exc) {
            // TODO: 这里记录方案要完善
            Log::critical($exc->getMessage(), $exc->getTrace());
            return response(['msg' => $exc->getMessage()], 500);
        } catch (\Exception $exc) {
            // 若在处理渲染报错时出错，记录错词日志并将错误交由框架处理
            $this->report($exc);
            return null;
        }
        return null;
    }
}