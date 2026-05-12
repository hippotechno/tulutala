<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\CoreExtension;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;
use Twig\TemplateWrapper;

/* /var/www/html/themes/hippo-default-theme/layouts/default.htm */
class __TwigTemplate_ed75039bd430812d5c0846a719c83340 extends Template
{
    private Source $source;
    /**
     * @var array<string, Template>
     */
    private array $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
        $this->sandbox = $this->extensions[SandboxExtension::class];
        $this->checkSecurity();
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 1
        yield "<!DOCTYPE html>
<html>
    <head>
        <meta charset=\"utf-8\">
        <title>";
        // line 5
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->sandbox->ensureToStringAllowed(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["this"] ?? null), "page", [], "any", false, false, true, 5), "title", [], "any", false, false, true, 5), 5, $this->source), "html", null, true);
        yield "</title>
        <meta name=\"description\" content=\"";
        // line 6
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->sandbox->ensureToStringAllowed(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["this"] ?? null), "page", [], "any", false, false, true, 6), "meta_description", [], "any", false, false, true, 6), 6, $this->source), "html", null, true);
        yield "\">
        <meta name=\"title\" content=\"";
        // line 7
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->sandbox->ensureToStringAllowed(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["this"] ?? null), "page", [], "any", false, false, true, 7), "meta_title", [], "any", false, false, true, 7), 7, $this->source), "html", null, true);
        yield "\">
        <meta name=\"author\" content=\"Silver Arrow Software\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
        <meta name=\"generator\" content=\"Silver Arrow Software\">

        <link rel=\"icon\" type=\"image/png\" href=\"";
        // line 12
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/images/favicon.ico");
        yield "\">
        <link rel=\"preconnect\" href=\"https://fonts.gstatic.com\"> 
        <link href=\"https://fonts.googleapis.com/css2?family=Patrick+Hand+SC&display=swap\" rel=\"stylesheet\">
        <link href=\"https://fonts.googleapis.com/css2?family=Quicksand:wght@400;700&display=swap\" rel=\"stylesheet\">
        <link href=\"";
        // line 16
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/css/great-vibes-font.css");
        yield "\" rel=\"stylesheet\">

        <link href=\"";
        // line 18
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/css/bootstrap.min.css");
        yield "\" rel=\"stylesheet\">
        <link href=\"";
        // line 19
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/vendor/animate/animate.min.css");
        yield "\" rel=\"stylesheet\">
        <link href=\"";
        // line 20
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/vendor/mdi-font/css/material-design-iconic-font.min.css");
        yield "\" rel=\"stylesheet\">
        <link href=\"";
        // line 21
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/vendor/linearicons-free/css/linearicons-free.css");
        yield "\" rel=\"stylesheet\">
        <link href=\"";
        // line 22
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/vendor/animsition/animsition.min.css");
        yield "\" rel=\"stylesheet\">
        <link href=\"";
        // line 23
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/vendor/css-hamburgers/hamburgers.min.css");
        yield "\" rel=\"stylesheet\">
        <link href=\"";
        // line 24
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/vendor/slick/slick.css");
        yield "\" rel=\"stylesheet\">
        <link href=\"";
        // line 25
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/vendor/lightbox2/css/lightbox.min.css");
        yield "\" rel=\"stylesheet\">
        <link href=\"";
        // line 26
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/vendor/select2/select2.min.css");
        yield "\" rel=\"stylesheet\">
        <link href=\"";
        // line 27
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/vendor/modalVideo/modal-video.min.css");
        yield "\" rel=\"stylesheet\">
        <link href=\"";
        // line 28
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/vendor/revolution/css/layers.css");
        yield "\" rel=\"stylesheet\">
        <link href=\"";
        // line 29
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/vendor/revolution/css/navigation.css");
        yield "\" rel=\"stylesheet\">
        <link href=\"";
        // line 30
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/vendor/revolution/css/settings.css");
        yield "\" rel=\"stylesheet\">
        <link href=\"";
        // line 31
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/css/theme.css");
        yield "\" rel=\"stylesheet\">
        ";
        // line 32
        echo $this->env->getExtension('Cms\Twig\Extension')->assetsFunction('css');
        echo $this->env->getExtension('Cms\Twig\Extension')->assetsFunction('vite');
        echo $this->env->getExtension('Cms\Twig\Extension')->displayBlock('styles');
        // line 33
        yield "    </head>
    <body class=\"animsition js-preloader\">
        <div class=\"page-wrapper\">
            <!-- Header -->
            <header id=\"header\">
                ";
        // line 38
        $context['__cms_partial_params'] = [];
        echo $this->env->getExtension('Cms\Twig\Extension')->partialFunction("header"        , $context['__cms_partial_params']        , true        );
        unset($context['__cms_partial_params']);
        // line 39
        yield "            </header>

            <!-- Content -->
            <main id=\"main\">
                ";
        // line 43
        echo $this->env->getExtension('Cms\Twig\Extension')->pageFunction();
        // line 44
        yield "
                <!-- Footer -->
                <footer class=\"footer\">
                    ";
        // line 47
        $context['__cms_partial_params'] = [];
        echo $this->env->getExtension('Cms\Twig\Extension')->partialFunction("footer"        , $context['__cms_partial_params']        , true        );
        unset($context['__cms_partial_params']);
        // line 48
        yield "                </footer>

                <section class=\"section copyright\">
                    <div class=\"container\">
                        <div class=\"row\">
                            <div class=\"col-md-12\">
                                <div class=\"copyright-wrap\">
                                    <ul class=\"list-unstyled list-inline list-social list-social-2\">
                                        <li class=\"list-inline-item\">
                                            <a class=\"ic-fb\" href=\"#\">
                                                <i class=\"zmdi zmdi-facebook-box\"></i>
                                            </a>
                                        </li>
                                        <li class=\"list-inline-item\">
                                            <a class=\"ic-twi\" href=\"#\">
                                                <i class=\"zmdi zmdi-twitter\"></i>
                                            </a>
                                        </li>
                                        <li class=\"list-inline-item\">
                                            <a class=\"ic-insta\" href=\"#\">
                                                <i class=\"zmdi zmdi-instagram\"></i>
                                            </a>
                                        </li>
                                    </ul>
                                    <span class=\"copyright__text\">© 2021 Hippo Technology Team.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        </div>

        <!-- Scripts -->
        <script src=\"";
        // line 82
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/js/jquery.min.js");
        yield "\"></script>
        <script src=\"";
        // line 83
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/js/bootstrap.bundle.min.js");
        yield "\"></script>
        <script src=\"";
        // line 84
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/vendor/animsition/animsition.min.js");
        yield "\"></script>
        <script src=\"";
        // line 85
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/vendor/slick/slick.min.js");
        yield "\"></script>
        <script src=\"";
        // line 86
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/vendor/lightbox2/js/lightbox.min.js");
        yield "\"></script>
        <script src=\"";
        // line 87
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/vendor/waypoints/jquery.waypoints.min.js");
        yield "\"></script>
        <script src=\"";
        // line 88
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/vendor/wow/wow.min.js");
        yield "\"></script>
        <script src=\"";
        // line 89
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/vendor/jquery.counterup/jquery.counterup.min.js");
        yield "\"></script>
        <script src=\"";
        // line 90
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/vendor/isotope/isotope.pkgd.min.js");
        yield "\"></script>
        <script src=\"";
        // line 91
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/vendor/isotope/imagesloaded.pkgd.min.js");
        yield "\"></script>
        <script src=\"";
        // line 92
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/vendor/matchHeight/jquery.matchHeight-min.js");
        yield "\"></script>
        <script src=\"";
        // line 93
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/vendor/select2/select2.min.js");
        yield "\"></script>
        <script src=\"";
        // line 94
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/vendor/sweetalert/sweetalert.min.js");
        yield "\"></script>
        <script src=\"";
        // line 95
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/vendor/bootstrap-progressbar/bootstrap-progressbar.min.js");
        yield "\"></script>
        <script src=\"";
        // line 96
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/vendor/noUiSlider/nouislider.min.js");
        yield "\"></script>
        <script src=\"";
        // line 97
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/vendor/modalVideo/modal-video.min.js");
        yield "\"></script>
        <script src=\"";
        // line 98
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/vendor/revolution/js/jquery.themepunch.tools.min.js");
        yield "\"></script>
        <script src=\"";
        // line 99
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/vendor/revolution/js/jquery.themepunch.revolution.min.js");
        yield "\"></script>

        <script src=\"";
        // line 101
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/vendor/revolution/js/extensions/revolution.extension.video.min.js");
        yield "\"></script>
        <script src=\"";
        // line 102
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/vendor/revolution/js/extensions/revolution.extension.slideanims.min.js");
        yield "\"></script>
        <script src=\"";
        // line 103
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/vendor/revolution/js/extensions/revolution.extension.actions.min.js");
        yield "\"></script>
        <script src=\"";
        // line 104
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/vendor/revolution/js/extensions/revolution.extension.layeranimation.min.js");
        yield "\"></script>
        <script src=\"";
        // line 105
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/vendor/revolution/js/extensions/revolution.extension.kenburn.min.js");
        yield "\"></script>
        <script src=\"";
        // line 106
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/vendor/revolution/js/extensions/revolution.extension.navigation.min.js");
        yield "\"></script>
        <script src=\"";
        // line 107
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/vendor/revolution/js/extensions/revolution.extension.migration.min.js");
        yield "\"></script>
        <script src=\"";
        // line 108
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/vendor/revolution/js/extensions/revolution.extension.parallax.min.js");
        yield "\"></script>
        <script src=\"";
        // line 109
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/js/config-revolution.min.js");
        yield "\"></script>

        <script src=\"";
        // line 111
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/js/app.js");
        yield "\"></script>
        ";
        // line 112
        $_minify = System\Classes\CombineAssets::instance()->useMinify;
        if ($_minify) {
            echo '<script src="https://tulutala-local.test/modules/system/assets/js/framework.combined-min.js?v=1.2.12"></script>'.PHP_EOL;
        }
        else {
            echo '<script src="https://tulutala-local.test/modules/system/assets/js/framework.js?v=1.2.12"></script>'.PHP_EOL;
            echo '<script src="https://tulutala-local.test/modules/system/assets/js/framework.extras.js?v=1.2.12"></script>'.PHP_EOL;
        }
        echo '<link rel="stylesheet" property="stylesheet" href="https://tulutala-local.test/modules/system/assets/css/framework.extras'.($_minify ? '-min' : '').'.css?v=1.2.12">'.PHP_EOL;
        unset($_minify);
        // line 113
        yield "        ";
        echo $this->env->getExtension('Cms\Twig\Extension')->assetsFunction('js');
        echo $this->env->getExtension('Cms\Twig\Extension')->displayBlock('scripts');
        // line 114
        yield "    </body>
</html>";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "/var/www/html/themes/hippo-default-theme/layouts/default.htm";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable(): bool
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo(): array
    {
        return array (  331 => 114,  327 => 113,  316 => 112,  312 => 111,  307 => 109,  303 => 108,  299 => 107,  295 => 106,  291 => 105,  287 => 104,  283 => 103,  279 => 102,  275 => 101,  270 => 99,  266 => 98,  262 => 97,  258 => 96,  254 => 95,  250 => 94,  246 => 93,  242 => 92,  238 => 91,  234 => 90,  230 => 89,  226 => 88,  222 => 87,  218 => 86,  214 => 85,  210 => 84,  206 => 83,  202 => 82,  166 => 48,  162 => 47,  157 => 44,  155 => 43,  149 => 39,  145 => 38,  138 => 33,  134 => 32,  130 => 31,  126 => 30,  122 => 29,  118 => 28,  114 => 27,  110 => 26,  106 => 25,  102 => 24,  98 => 23,  94 => 22,  90 => 21,  86 => 20,  82 => 19,  78 => 18,  73 => 16,  66 => 12,  58 => 7,  54 => 6,  50 => 5,  44 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("<!DOCTYPE html>
<html>
    <head>
        <meta charset=\"utf-8\">
        <title>{{ this.page.title }}</title>
        <meta name=\"description\" content=\"{{ this.page.meta_description }}\">
        <meta name=\"title\" content=\"{{ this.page.meta_title }}\">
        <meta name=\"author\" content=\"Silver Arrow Software\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
        <meta name=\"generator\" content=\"Silver Arrow Software\">

        <link rel=\"icon\" type=\"image/png\" href=\"{{ 'assets/images/favicon.ico'|theme }}\">
        <link rel=\"preconnect\" href=\"https://fonts.gstatic.com\"> 
        <link href=\"https://fonts.googleapis.com/css2?family=Patrick+Hand+SC&display=swap\" rel=\"stylesheet\">
        <link href=\"https://fonts.googleapis.com/css2?family=Quicksand:wght@400;700&display=swap\" rel=\"stylesheet\">
        <link href=\"{{ 'assets/css/great-vibes-font.css'|theme }}\" rel=\"stylesheet\">

        <link href=\"{{ 'assets/css/bootstrap.min.css'|theme }}\" rel=\"stylesheet\">
        <link href=\"{{ 'assets/vendor/animate/animate.min.css'|theme }}\" rel=\"stylesheet\">
        <link href=\"{{ 'assets/vendor/mdi-font/css/material-design-iconic-font.min.css'|theme }}\" rel=\"stylesheet\">
        <link href=\"{{ 'assets/vendor/linearicons-free/css/linearicons-free.css'|theme }}\" rel=\"stylesheet\">
        <link href=\"{{ 'assets/vendor/animsition/animsition.min.css'|theme }}\" rel=\"stylesheet\">
        <link href=\"{{ 'assets/vendor/css-hamburgers/hamburgers.min.css'|theme }}\" rel=\"stylesheet\">
        <link href=\"{{ 'assets/vendor/slick/slick.css'|theme }}\" rel=\"stylesheet\">
        <link href=\"{{ 'assets/vendor/lightbox2/css/lightbox.min.css'|theme }}\" rel=\"stylesheet\">
        <link href=\"{{ 'assets/vendor/select2/select2.min.css'|theme }}\" rel=\"stylesheet\">
        <link href=\"{{ 'assets/vendor/modalVideo/modal-video.min.css'|theme }}\" rel=\"stylesheet\">
        <link href=\"{{ 'assets/vendor/revolution/css/layers.css'|theme }}\" rel=\"stylesheet\">
        <link href=\"{{ 'assets/vendor/revolution/css/navigation.css'|theme }}\" rel=\"stylesheet\">
        <link href=\"{{ 'assets/vendor/revolution/css/settings.css'|theme }}\" rel=\"stylesheet\">
        <link href=\"{{ 'assets/css/theme.css'|theme }}\" rel=\"stylesheet\">
        {% styles %}
    </head>
    <body class=\"animsition js-preloader\">
        <div class=\"page-wrapper\">
            <!-- Header -->
            <header id=\"header\">
                {% partial 'header' %}
            </header>

            <!-- Content -->
            <main id=\"main\">
                {% page %}

                <!-- Footer -->
                <footer class=\"footer\">
                    {% partial 'footer' %}
                </footer>

                <section class=\"section copyright\">
                    <div class=\"container\">
                        <div class=\"row\">
                            <div class=\"col-md-12\">
                                <div class=\"copyright-wrap\">
                                    <ul class=\"list-unstyled list-inline list-social list-social-2\">
                                        <li class=\"list-inline-item\">
                                            <a class=\"ic-fb\" href=\"#\">
                                                <i class=\"zmdi zmdi-facebook-box\"></i>
                                            </a>
                                        </li>
                                        <li class=\"list-inline-item\">
                                            <a class=\"ic-twi\" href=\"#\">
                                                <i class=\"zmdi zmdi-twitter\"></i>
                                            </a>
                                        </li>
                                        <li class=\"list-inline-item\">
                                            <a class=\"ic-insta\" href=\"#\">
                                                <i class=\"zmdi zmdi-instagram\"></i>
                                            </a>
                                        </li>
                                    </ul>
                                    <span class=\"copyright__text\">© 2021 Hippo Technology Team.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        </div>

        <!-- Scripts -->
        <script src=\"{{ 'assets/js/jquery.min.js'|theme }}\"></script>
        <script src=\"{{ 'assets/js/bootstrap.bundle.min.js'|theme }}\"></script>
        <script src=\"{{ 'assets/vendor/animsition/animsition.min.js'|theme }}\"></script>
        <script src=\"{{ 'assets/vendor/slick/slick.min.js'|theme }}\"></script>
        <script src=\"{{ 'assets/vendor/lightbox2/js/lightbox.min.js'|theme }}\"></script>
        <script src=\"{{ 'assets/vendor/waypoints/jquery.waypoints.min.js'|theme }}\"></script>
        <script src=\"{{ 'assets/vendor/wow/wow.min.js'|theme }}\"></script>
        <script src=\"{{ 'assets/vendor/jquery.counterup/jquery.counterup.min.js'|theme }}\"></script>
        <script src=\"{{ 'assets/vendor/isotope/isotope.pkgd.min.js'|theme }}\"></script>
        <script src=\"{{ 'assets/vendor/isotope/imagesloaded.pkgd.min.js'|theme }}\"></script>
        <script src=\"{{ 'assets/vendor/matchHeight/jquery.matchHeight-min.js'|theme }}\"></script>
        <script src=\"{{ 'assets/vendor/select2/select2.min.js'|theme }}\"></script>
        <script src=\"{{ 'assets/vendor/sweetalert/sweetalert.min.js'|theme }}\"></script>
        <script src=\"{{ 'assets/vendor/bootstrap-progressbar/bootstrap-progressbar.min.js'|theme }}\"></script>
        <script src=\"{{ 'assets/vendor/noUiSlider/nouislider.min.js'|theme }}\"></script>
        <script src=\"{{ 'assets/vendor/modalVideo/modal-video.min.js'|theme }}\"></script>
        <script src=\"{{ 'assets/vendor/revolution/js/jquery.themepunch.tools.min.js'|theme }}\"></script>
        <script src=\"{{ 'assets/vendor/revolution/js/jquery.themepunch.revolution.min.js'|theme }}\"></script>

        <script src=\"{{ 'assets/vendor/revolution/js/extensions/revolution.extension.video.min.js'|theme }}\"></script>
        <script src=\"{{ 'assets/vendor/revolution/js/extensions/revolution.extension.slideanims.min.js'|theme }}\"></script>
        <script src=\"{{ 'assets/vendor/revolution/js/extensions/revolution.extension.actions.min.js'|theme }}\"></script>
        <script src=\"{{ 'assets/vendor/revolution/js/extensions/revolution.extension.layeranimation.min.js'|theme }}\"></script>
        <script src=\"{{ 'assets/vendor/revolution/js/extensions/revolution.extension.kenburn.min.js'|theme }}\"></script>
        <script src=\"{{ 'assets/vendor/revolution/js/extensions/revolution.extension.navigation.min.js'|theme }}\"></script>
        <script src=\"{{ 'assets/vendor/revolution/js/extensions/revolution.extension.migration.min.js'|theme }}\"></script>
        <script src=\"{{ 'assets/vendor/revolution/js/extensions/revolution.extension.parallax.min.js'|theme }}\"></script>
        <script src=\"{{ 'assets/js/config-revolution.min.js'|theme }}\"></script>

        <script src=\"{{ 'assets/js/app.js'|theme }}\"></script>
        {% framework extras %}
        {% scripts %}
    </body>
</html>", "/var/www/html/themes/hippo-default-theme/layouts/default.htm", "");
    }
    
    public function checkSecurity()
    {
        static $tags = ["styles" => 32, "partial" => 38, "page" => 43, "framework" => 112, "scripts" => 113];
        static $filters = ["escape" => 5, "theme" => 12];
        static $functions = [];

        try {
            $this->sandbox->checkSecurity(
                ['styles', 'partial', 'page', 'framework', 'scripts'],
                ['escape', 'theme'],
                [],
                $this->source
            );
        } catch (SecurityError $e) {
            $e->setSourceContext($this->source);

            if ($e instanceof SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

    }
}
