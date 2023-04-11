<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Exceptions\CustomValidationException;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    Use ApiResponse;
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (Exception $e, $request) {
            if($request->is('api/*')){

                //Tratamento de excepões do tipo HttpException
                if($e instanceof NotFoundHttpException){   
                    return $this->errorResponse('The specified URL cannot be found',404);               
                }

                if($e instanceof MethodNotAllowedHttpException){   
                    return $this->errorResponse('The specified method for the request is invalid',405);               
                }

                if($e instanceof ModelNotFoundException){
                    $modelName = strtolower(class_basename($e->getModel()));

                    return $this->errorResponse('Does not exists any {$modelName} with the specified identificator',404);
                }

                if($e instanceof AuthenticationException){
                    return $this->errorResponse('Unauthenticated.',401);
                }

                if($e instanceof AuthorizationException){
                    return $this->errorResponse($e->getMessage(),403);
                }

                //Uma forma generica de tratar as excepões do tipo HttpException, ou seja, qualque excepção que houver deste tipo, será trata aqui!
                if($e instanceof HttpException){
                    return $this->errorResponse($e->getMessage(),$e->getStatusCode());
                }

                //Tratamento de excepões do tipo QueryException
                if($e instanceof QueryException){   
                    $errorCode = $e->errorInfo[1];
                    
                    if($errorCode == 1451){
                        return $this->errorResponse('Cannot remove this resource permanently. It is related with any other resource',409);
                    }

                    if($errorCode == 1062){
                        return $this->errorResponse('Cannot save this recorde because integrity constraint violation.',409);
                    }
                    
                }
                //Verrificando se estamos em produção ou de debug, 
                //se estivermos em produção vamos retornar esse erro compreesivel para todos, 
                //caso contrario vamos ter o erro padrão com mais detalhes!
                if(!config('app.debug')){
                    return $this->errorResponse('Unexpected Exception. Try Later',500);
                }
            }
        });
        
    }
}
