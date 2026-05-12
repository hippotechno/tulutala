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

/* /var/www/html/themes/hippo-default-theme/partials/footer.htm */
class __TwigTemplate_5e12be24a9fcdee5dcef75529c377c84 extends Template
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
        yield "<div class=\"container\">
    <div class=\"col-md-12\">
        <div class=\"footer-wrap\">
            <div class=\"footer__logo\">
                <a href=\"#\">
                    <img src=\"";
        // line 6
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/images/landing/icon/logo.png");
        yield "\" alt=\"tulutula\" />
                </a>
            </div>
            <ul class=\"list-unstyled list-inline footer__list\">
                <li class=\"list-inline-item\">
                    <a href=\"#\">Trang chủ</a>
                </li>
                <li class=\"list-inline-item\">
                    <a href=\"#\">Tulutula là gì?</a>
                </li>
                <li class=\"list-inline-item\">
                    <a href=\"#\">Nhà chia sẻ</a>
                </li>
                <li class=\"list-inline-item\">
                    <a href=\"#\">Hẻm đồ chơi</a>
                </li>
                <li class=\"list-inline-item\">
                    <a href=\"#\">Phòng kết nối</a>
                </li>
                <li class=\"list-inline-item\">
                    <a href=\"/app\">Tham gia ngay</a>
                </li>
            </ul>
            <form class=\"form form--icon form__footer\" method=\"post\">
                <input type=\"email\" name=\"email-footer\" placeholder=\"Nhập email để nhận tin mới nhất...\" />
                <button class=\"btn-submit-1\" type=\"submit\">
                    <img src=\"";
        // line 32
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/images/landing/icon/send.png");
        yield "\" alt=\"send\" />
                </button>
            </form>
        </div>
    </div>
</div>";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "/var/www/html/themes/hippo-default-theme/partials/footer.htm";
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
        return array (  80 => 32,  51 => 6,  44 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("<div class=\"container\">
    <div class=\"col-md-12\">
        <div class=\"footer-wrap\">
            <div class=\"footer__logo\">
                <a href=\"#\">
                    <img src=\"{{ 'assets/images/landing/icon/logo.png'|theme }}\" alt=\"tulutula\" />
                </a>
            </div>
            <ul class=\"list-unstyled list-inline footer__list\">
                <li class=\"list-inline-item\">
                    <a href=\"#\">Trang chủ</a>
                </li>
                <li class=\"list-inline-item\">
                    <a href=\"#\">Tulutula là gì?</a>
                </li>
                <li class=\"list-inline-item\">
                    <a href=\"#\">Nhà chia sẻ</a>
                </li>
                <li class=\"list-inline-item\">
                    <a href=\"#\">Hẻm đồ chơi</a>
                </li>
                <li class=\"list-inline-item\">
                    <a href=\"#\">Phòng kết nối</a>
                </li>
                <li class=\"list-inline-item\">
                    <a href=\"/app\">Tham gia ngay</a>
                </li>
            </ul>
            <form class=\"form form--icon form__footer\" method=\"post\">
                <input type=\"email\" name=\"email-footer\" placeholder=\"Nhập email để nhận tin mới nhất...\" />
                <button class=\"btn-submit-1\" type=\"submit\">
                    <img src=\"{{ 'assets/images/landing/icon/send.png'|theme }}\" alt=\"send\" />
                </button>
            </form>
        </div>
    </div>
</div>", "/var/www/html/themes/hippo-default-theme/partials/footer.htm", "");
    }
    
    public function checkSecurity()
    {
        static $tags = [];
        static $filters = ["theme" => 6];
        static $functions = [];

        try {
            $this->sandbox->checkSecurity(
                [],
                ['theme'],
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
