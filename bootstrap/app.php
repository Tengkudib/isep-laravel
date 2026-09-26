<?php

use App\Http\Middleware\EnsureRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\TrimStrings;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['role' => EnsureRole::class]);

        // Sistem asal memangkas input secara manual di tempat yang perlu sahaja (bukan kata laluan),
        // dan membezakan '' daripada null - jadi matikan transformasi input automatik Laravel.
        $middleware->remove([TrimStrings::class, ConvertEmptyStringsToNull::class]);

        // Kuki bahasa ditetapkan terus oleh JavaScript (assets/theme-toggle.js), jadi jangan dienkripsi.
        $middleware->encryptCookies(except: ['isep_lang']);

        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn (Request $request) => dashboard_route_for($request->user()?->role));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Fail muat naik melebihi post_max_size - kembali ke borang dengan mesej mesra
        $exceptions->render(function (PostTooLargeException $e, Request $request) {
            $msg = t('Fail terlalu besar untuk pelayan menerimanya.', 'File is too large for the server to accept.');
            if ($request->expectsJson()) {
                return response()->json(['error' => $msg], 413);
            }

            return back()->with('error', $msg);
        });

        $exceptions->render(function (HttpException $e, Request $request) {
            // 419: sesi luput / token CSRF tidak sah - kekalkan mesej asal sistem
            if ($e->getStatusCode() === 419) {
                $msg = t('Sesi luput atau permintaan tidak sah. Sila muat semula halaman dan cuba lagi.', 'Session expired or invalid request. Please reload the page and try again.');

                return $request->expectsJson()
                    ? response()->json(['error' => $msg], 419)
                    : response($msg, 419);
            }

            // 403 dari middleware peranan - teks biasa seperti sistem asal
            if ($e->getStatusCode() === 403 && ! $request->expectsJson()) {
                return response($e->getMessage() ?: 'Forbidden', 403);
            }

            return null;
        });
    })->create();
