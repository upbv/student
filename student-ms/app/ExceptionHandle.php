<?php
namespace app;

use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\Response;
use Throwable;

/**
 * 应用异常处理类
 */
class ExceptionHandle extends Handle
{
    /**
     * 不需要记录信息（日志）的异常类列表
     * @var array
     */
    protected $ignoreReport = [
        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        DataNotFoundException::class,
        ValidateException::class,
    ];

    /**
     * 记录异常信息（包括日志或者其它方式记录）
     *
     * @access public
     * @param  Throwable $exception
     * @return void
     */
    public function report(Throwable $exception): void
    {
        // 使用内置的方式记录异常日志
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @access public
     * @param \think\Request   $request
     * @param Throwable $e
     * @return Response
     */
    public function render($request, Throwable $e): Response
    {
        // 调试模式下直接输出异常详情，便于定位问题
        if (env('app_debug')) {
            return response(
                get_class($e) . ': ' . $e->getMessage() . "\n" .
                $e->getFile() . ':' . $e->getLine() . "\n\n" .
                $e->getTraceAsString(),
                500
            );
        }

        // 接口请求返回统一 JSON，避免前端拿到整页 HTML 无法解析
        if ($request->isJson() || $request->isAjax() || $request->method() === 'POST') {
            return json(['code' => 0, 'msg' => '系统处理请求时出现异常，请稍后重试'], 500);
        }

        // 页面请求返回统一的错误提示页
        return response(view('error/500'), 500);
    }}
