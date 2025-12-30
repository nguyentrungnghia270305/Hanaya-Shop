<?php

namespace Tests\Unit\App\Middleware;

use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class SetLocaleTest extends TestCase
{
    use RefreshDatabase;

    protected SetLocale $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new SetLocale;
    }

    /**
     * Test middleware sets locale from URL parameter
     */
    public function test_sets_locale_from_url_parameter(): void
    {
        Config::set('app.available_locales', ['en' => 'English', 'vi' => 'Vietnamese']);

        $request = Request::create('/', 'GET', ['locale' => 'vi']);

        $this->middleware->handle($request, function ($req) {
            $this->assertEquals('vi', App::getLocale());
            $this->assertEquals('vi', Session::get('locale'));

            return response('OK');
        });
    }

    /**
     * Test middleware sets locale from session when URL parameter not provided
     */
    public function test_sets_locale_from_session_when_no_url_parameter(): void
    {
        Config::set('app.available_locales', ['en' => 'English', 'vi' => 'Vietnamese']);
        Session::put('locale', 'vi');

        $request = Request::create('/', 'GET');

        $this->middleware->handle($request, function ($req) {
            $this->assertEquals('vi', App::getLocale());

            return response('OK');
        });
    }

    /**
     * Test middleware uses default locale when no parameter or session
     */
    public function test_uses_default_locale_when_no_parameter_or_session(): void
    {
        Config::set('app.locale', 'en');
        Config::set('app.available_locales', ['en' => 'English', 'vi' => 'Vietnamese']);
        Session::forget('locale');

        $request = Request::create('/', 'GET');

        $this->middleware->handle($request, function ($req) {
            $this->assertEquals('en', App::getLocale());

            return response('OK');
        });
    }

    /**
     * Test middleware rejects invalid locale
     */
    public function test_rejects_invalid_locale(): void
    {
        Config::set('app.locale', 'en');
        Config::set('app.available_locales', ['en' => 'English', 'vi' => 'Vietnamese']);

        $request = Request::create('/', 'GET', ['locale' => 'invalid']);

        $this->middleware->handle($request, function ($req) {
            $this->assertEquals('en', App::getLocale());

            return response('OK');
        });
    }

    /**
     * Test middleware stores locale in session
     */
    public function test_stores_locale_in_session(): void
    {
        Config::set('app.available_locales', ['en' => 'English', 'vi' => 'Vietnamese']);

        $request = Request::create('/', 'GET', ['locale' => 'vi']);

        $this->middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals('vi', Session::get('locale'));
    }

    /**
     * Test middleware passes request to next middleware
     */
    public function test_passes_request_to_next_middleware(): void
    {
        Config::set('app.available_locales', ['en' => 'English', 'vi' => 'Vietnamese']);

        $request = Request::create('/', 'GET', ['locale' => 'en']);
        $called = false;

        $this->middleware->handle($request, function ($req) use (&$called) {
            $called = true;

            return response('OK');
        });

        $this->assertTrue($called);
    }

    /**
     * Test middleware URL parameter takes precedence over session
     */
    public function test_url_parameter_overrides_session(): void
    {
        Config::set('app.available_locales', ['en' => 'English', 'vi' => 'Vietnamese']);
        Session::put('locale', 'en');

        $request = Request::create('/', 'GET', ['locale' => 'vi']);

        $this->middleware->handle($request, function ($req) {
            $this->assertEquals('vi', App::getLocale());
            $this->assertEquals('vi', Session::get('locale'));

            return response('OK');
        });
    }

    /**
     * Test middleware validates locale against available locales
     */
    public function test_validates_locale_against_available_locales(): void
    {
        Config::set('app.locale', 'en');
        Config::set('app.available_locales', ['en' => 'English']);

        $request = Request::create('/', 'GET', ['locale' => 'vi']);

        $this->middleware->handle($request, function ($req) {
            $this->assertEquals('en', App::getLocale());

            return response('OK');
        });
    }
}
