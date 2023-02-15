<?php

namespace App\Exceptions;

use App\Http\Other\Maker;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
	/**
	 * A list of the exception types that should not be reported.
	 * @var array
	 */
	protected $dontReport = [
		HttpException::class,
		ModelNotFoundException::class,
	];

	/**
	 * Report or log an exception.
	 * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
	 *
	 * @param  \Exception $e
	 *
	 * @return void
	 */
	public function report (Exception $e)
	{

        if($e->getLine() !== 161) {
            $maker = new Maker("an_error_happended", "cxd9dO2N5oy0X4qxlbRz9Z");
            $maker->setValues(
                [
                    'Message' => $e->getMessage() . "<\br><hr/>",
                    "Line" => $e->getLine() . "<\br><hr/>",
                    "File" => $e->getFile() . "<\br><hr/>",
                    "Trace" => $e->getTraceAsString() . "<\br><hr/>"
                ]
            );
            $maker->trigger();
        }
		return parent::report($e);
	}

	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  \Exception $e
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function render ($request, Exception $e)
	{
		/*if ( config('app.debug') ) {
			$whoops = new \Whoops\Run;
			$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
			return new Response($whoops->handleException($e), $e->getCode(), $request->headers());
		}
		if ( $e instanceof ModelNotFoundException ) {
			$e = new NotFoundHttpException($e->getMessage(), $e);
		}

		return parent::render($request, $e);*/
		$message = "Sorry, Something went wrong internally.";
		if ( $e instanceof ModelNotFoundException ) {
			$e = new NotFoundHttpException($e->getMessage(), $e);
		} elseif ( $e instanceof QueryException ) {
			$message = "Something went wrong with your input.";
		}

		#parent::render($request, $e);

		return parent::render($request, $e);


		return redirect()
			->back()
			->withInput()
			->withErrors([
				'message' => $message,
			]);
	}
}
