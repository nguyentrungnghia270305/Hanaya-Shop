<?php

namespace Tests\Unit\App\Controllers\Common;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class LocaleControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up available locales
        Config::set('app.available_locales', [
            'en' => 'English',
            'vi' => 'Tiếng Việt',
            'jp' => '日本語',
        ]);
    }

    /**
     * Test setLocale with valid English locale
     */
    public function test_set_locale_with_valid_english_locale(): void
    {
        $response = $this->get(route('locale.set', ['locale' => 'en']));

        $response->assertRedirect();
        $this->assertEquals('en', Session::get('locale'));
    }

    /**
     * Test setLocale with valid Vietnamese locale
     */
    public function test_set_locale_with_valid_vietnamese_locale(): void
    {
        $response = $this->get(route('locale.set', ['locale' => 'vi']));

        $response->assertRedirect();
        $this->assertEquals('vi', Session::get('locale'));
    }

    /**
     * Test setLocale with valid Japanese locale
     */
    public function test_set_locale_with_valid_japanese_locale(): void
    {
        $response = $this->get(route('locale.set', ['locale' => 'jp']));

        $response->assertRedirect();
        $this->assertEquals('jp', Session::get('locale'));
    }

    /**
     * Test setLocale with invalid locale returns 404
     */
    public function test_set_locale_with_invalid_locale_returns_404(): void
    {
        $response = $this->get(route('locale.set', ['locale' => 'fr']));

        $response->assertStatus(404);
    }

    /**
     * Test setLocale with empty locale returns 404
     */
    public function test_set_locale_with_empty_locale_returns_404(): void
    {
        // Can't use route() with empty parameter, test direct URL instead
        $response = $this->get('/locale/');

        $response->assertStatus(404);
    }

    /**
     * Test setLocale with unsupported locale returns 404
     */
    public function test_set_locale_with_unsupported_locale_returns_404(): void
    {
        $response = $this->get(route('locale.set', ['locale' => 'de']));

        $response->assertStatus(404);
    }

    /**
     * Test setLocale redirects back to previous page
     */
    public function test_set_locale_redirects_back(): void
    {
        $response = $this->from('/products')
            ->get(route('locale.set', ['locale' => 'vi']));

        $response->assertRedirect('/products');
    }

    /**
     * Test setLocale stores locale in session
     */
    public function test_set_locale_stores_in_session(): void
    {
        $this->assertNull(Session::get('locale'));

        $this->get(route('locale.set', ['locale' => 'vi']));

        $this->assertTrue(Session::has('locale'));
        $this->assertEquals('vi', Session::get('locale'));
    }

    /**
     * Test setLocale overwrites previous locale
     */
    public function test_set_locale_overwrites_previous_locale(): void
    {
        Session::put('locale', 'en');
        $this->assertEquals('en', Session::get('locale'));

        $this->get(route('locale.set', ['locale' => 'jp']));

        $this->assertEquals('jp', Session::get('locale'));
    }

    /**
     * Test setLocale with mixed case locale
     */
    public function test_set_locale_case_sensitivity(): void
    {
        $response = $this->get(route('locale.set', ['locale' => 'EN']));

        $response->assertStatus(404);
    }

    /**
     * Test setLocale validates against config
     */
    public function test_set_locale_validates_against_config(): void
    {
        Config::set('app.available_locales', [
            'en' => 'English',
            'vi' => 'Tiếng Việt',
        ]);

        $response = $this->get(route('locale.set', ['locale' => 'jp']));

        $response->assertStatus(404);
    }

    /**
     * Test setLocale with numeric locale returns 404
     */
    public function test_set_locale_with_numeric_value_returns_404(): void
    {
        $response = $this->get(route('locale.set', ['locale' => '123']));

        $response->assertStatus(404);
    }

    /**
     * Test setLocale with special characters returns 404
     */
    public function test_set_locale_with_special_characters_returns_404(): void
    {
        $response = $this->get(route('locale.set', ['locale' => 'en-US']));

        $response->assertStatus(404);
    }

    /**
     * Test setLocale maintains session data
     */
    public function test_set_locale_maintains_other_session_data(): void
    {
        Session::put('user_data', ['name' => 'Test User']);

        $this->get(route('locale.set', ['locale' => 'vi']));

        $this->assertEquals(['name' => 'Test User'], Session::get('user_data'));
        $this->assertEquals('vi', Session::get('locale'));
    }

    /**
     * Test setLocale can be called multiple times
     */
    public function test_set_locale_can_be_called_multiple_times(): void
    {
        $this->get(route('locale.set', ['locale' => 'en']));
        $this->assertEquals('en', Session::get('locale'));

        $this->get(route('locale.set', ['locale' => 'vi']));
        $this->assertEquals('vi', Session::get('locale'));

        $this->get(route('locale.set', ['locale' => 'jp']));
        $this->assertEquals('jp', Session::get('locale'));
    }

    /**
     * Test setLocale with all supported locales
     */
    public function test_set_locale_supports_all_configured_locales(): void
    {
        $locales = Config::get('app.available_locales');

        foreach (array_keys($locales) as $locale) {
            Session::forget('locale');
            
            $response = $this->get(route('locale.set', ['locale' => $locale]));

            $response->assertRedirect();
            $this->assertEquals($locale, Session::get('locale'));
        }
    }
}
