<?php namespace RainLab\Translate\Components;

use Request;
use Redirect;
use RainLab\Translate\Models\Locale as LocaleModel;
use RainLab\Translate\Classes\Translator;
use Cms\Classes\ComponentBase;

class LocalePicker extends ComponentBase
{
    /**
     * @var RainLab\Translate\Classes\Translator Translator object.
     */
    protected $translator;

    /**
     * @var array Collection of enabled locales.
     */
    public $locales;

    /**
     * @var string The active locale code.
     */
    public $activeLocale;

    public function componentDetails()
    {
        return [
            'name'        => 'rainlab.translate::lang.locale_picker.component_name',
            'description' => 'rainlab.translate::lang.locale_picker.component_description',
        ];
    }

    public function defineProperties()
    {
        return [
            'forceUrl' => [
                'title'       => 'Force URL schema',
                'description' => 'Always prefix the URL with a language code.',
                'default'     => 0,
                'type'        => 'checkbox'
            ],
        ];
    }

    public function init()
    {
        $this->translator = Translator::instance();
    }

    public function onRun()
    {
        if ($redirect = $this->redirectForceUrl()) {
            return $redirect;
        }

        $this->page['activeLocale'] = $this->activeLocale = $this->translator->getLocale();
        $this->page['locales'] = $this->locales = LocaleModel::listEnabled();
    }

    public function onSwitchLocale()
    {
        if (!$locale = post('locale')) {
            return;
        }

        $this->translator->setLocale($locale);

        if ($this->property('forceUrl')) {
            return Redirect::to($this->translator->getCurrentPathInLocale($locale));
        }

        return Redirect::refresh();
    }

    protected function redirectForceUrl()
    {
        if (
            Request::ajax() ||
            !$this->property('forceUrl') ||
            $this->translator->loadLocaleFromRequest()
        ) {
            return;
        }

        $locale = $this->translator->getLocale(true)
            ?: $this->translator->getDefaultLocale();

        return Redirect::to($this->translator->getCurrentPathInLocale($locale));
    }
}
