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

/* /var/www/html/themes/hippo-default-theme/partials/header.htm */
class __TwigTemplate_791e54893e92e3e138e420c42a4a4e9b extends Template
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
        yield "<section class=\"top-contact-2 p-md-t-15\">
    <div class=\"wrap wrap--w1790\">
        <div class=\"container-fluid\">
            <div class=\"top-contact-row\">
                <div class=\"top-contact-col\">
                    <div class=\"top-contact__item\">
                        <span class=\"lnr lnr-map\"></span>Thành phố Hồ Chí Minh</div>
                    <div class=\"top-contact__item\">
                        <span class=\"lnr lnr-phone-handset\"></span>0908 01 27 27</div>
                </div>
                <div class=\"top-contact-col top-contact-col--alig-center d-none d-lg-flex\">
                    <div class=\"logo\">
                        <a href=\"#\">
                            <img src=\"";
        // line 14
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/images/landing/icon/logo.png");
        yield "\" alt=\"tulutula\" />
                        </a>
                    </div>
                </div>
                <div class=\"top-contact-col top-contact-col--alig-right\">
                    <ul class=\"list-unstyled list-inline list-social\">
                        <li class=\"list-inline-item\">
                            <a class=\"ic-fb\" href=\"#\">
                                <i class=\"zmdi zmdi-facebook-box\"></i>
                            </a>
                        </li>
                       
                       
                        <li class=\"list-inline-item seprator\">
                            <span></span>
                        </li>
                        <li class=\"list-inline-item\">
                            <a href=\"#\" data-toggle=\"modal\" data-target=\"#modal-search\">
                                <i class=\"fas fa-search\"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
<div class=\"header header-2 d-none d-lg-block js-header-1\">
    <div class=\"header__bar\">
        <div class=\"container\">
            <div class=\"header__content\">
                <nav class=\"header-navbar\">
                    <ul class=\"list-unstyled\">
                        <li class=\"header-navbar__item has-sub\">
                            <a href=\"#\">
                                <span class=\"bg-link\">Trang chủ</span>
                            </a>
                            
                        </li>
                        <li class=\"header-navbar__item has-sub\">
                            <a href=\"#\">
                                <span class=\"bg-link\">Tulutula là gì?</span>
                            </a>
                            
                        </li>
                        <li class=\"header-navbar__item has-sub\">
                            <a href=\"#\">
                                <span class=\"bg-link\">Nhà chia sẻ</span>
                            </a>
                           
                        </li>
                        <li class=\"header-navbar__item has-sub\">
                            <a href=\"#\">
                                <span class=\"bg-link\">Hẻm đồ chơi</span>
                            </a>
                           
                        </li>
                        <li class=\"header-navbar__item has-sub\">
                            <a href=\"#\">
                                <span class=\"bg-link\">Phòng kết nối</span>
                            </a>
                           
                        </li>
                        <li class=\"header-navbar__item has-sub\">
                            <a href=\"/app\">
                                <span class=\"bg-link\">Tham gia ngay</span>
                            </a>
                           
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>
<div class=\"modal fade\" id=\"modal-search\" role=\"dialog\">
    <button class=\"close\" type=\"button\" data-dismiss=\"modal\">
        <i class=\"zmdi zmdi-close\"></i>
    </button>
    <div class=\"modal-dialog\">
        <div class=\"modal-content\">
            <div class=\"modal-body\">
                <form class=\"form form--icon\" method=\"POST\">
                    <input type=\"text\" name=\"search\" placeholder=\"Search here...\" />
                    <button class=\"btn-submit-1\" type=\"submit\">
                        <i class=\"fa fa-search\"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<div class=\"header-mobile js-header-mobile d-block d-lg-none\">
    <div class=\"header-mobile__bar\">
        <div class=\"container-fluid clearfix\">
            <a class=\"logo\" href=\"index1.html\">
                <img src=\"";
        // line 110
        yield $this->extensions['Cms\Twig\Extension']->multiSiteThemeFilter("assets/images/landing/icon/logo.png");
        yield "\" alt=\"Jooby\" />
            </a>
            <button class=\"hamburger hamburger--slider float-right\" type=\"button\">
                <span class=\"hamburger-box\">
                    <span class=\"hamburger-inner\"></span>
                </span>
            </button>
        </div>
    </div>
    <nav class=\"navbar-mobile\">
        <ul class=\"navbar-mobile__list list-unstyled\">
            <li class=\"has-sub\">
                <a href=\"#\">Trang chủ</a>
            </li>
            <li class=\"has-sub\">
                <a href=\"#\">Tulutula là gì?</a>
            </li>
            <li class=\"has-sub\">
                <a href=\"#\">Nhà chia sẻ</a>
            </li>
            <li class=\"has-sub\">
                <a href=\"#\">Hẻm đồ chơi</a>
            </li>
            <li class=\"has-sub\">
                <a href=\"#\">Phòng kết nối</a>
            </li>
            <li class=\"has-sub\">
                <a href=\"#\">Tham gia ngay</a>
            </li>
           
        </ul>
    </nav>
</div>";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "/var/www/html/themes/hippo-default-theme/partials/header.htm";
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
        return array (  158 => 110,  59 => 14,  44 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("<section class=\"top-contact-2 p-md-t-15\">
    <div class=\"wrap wrap--w1790\">
        <div class=\"container-fluid\">
            <div class=\"top-contact-row\">
                <div class=\"top-contact-col\">
                    <div class=\"top-contact__item\">
                        <span class=\"lnr lnr-map\"></span>Thành phố Hồ Chí Minh</div>
                    <div class=\"top-contact__item\">
                        <span class=\"lnr lnr-phone-handset\"></span>0908 01 27 27</div>
                </div>
                <div class=\"top-contact-col top-contact-col--alig-center d-none d-lg-flex\">
                    <div class=\"logo\">
                        <a href=\"#\">
                            <img src=\"{{ 'assets/images/landing/icon/logo.png'|theme }}\" alt=\"tulutula\" />
                        </a>
                    </div>
                </div>
                <div class=\"top-contact-col top-contact-col--alig-right\">
                    <ul class=\"list-unstyled list-inline list-social\">
                        <li class=\"list-inline-item\">
                            <a class=\"ic-fb\" href=\"#\">
                                <i class=\"zmdi zmdi-facebook-box\"></i>
                            </a>
                        </li>
                       
                       
                        <li class=\"list-inline-item seprator\">
                            <span></span>
                        </li>
                        <li class=\"list-inline-item\">
                            <a href=\"#\" data-toggle=\"modal\" data-target=\"#modal-search\">
                                <i class=\"fas fa-search\"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
<div class=\"header header-2 d-none d-lg-block js-header-1\">
    <div class=\"header__bar\">
        <div class=\"container\">
            <div class=\"header__content\">
                <nav class=\"header-navbar\">
                    <ul class=\"list-unstyled\">
                        <li class=\"header-navbar__item has-sub\">
                            <a href=\"#\">
                                <span class=\"bg-link\">Trang chủ</span>
                            </a>
                            
                        </li>
                        <li class=\"header-navbar__item has-sub\">
                            <a href=\"#\">
                                <span class=\"bg-link\">Tulutula là gì?</span>
                            </a>
                            
                        </li>
                        <li class=\"header-navbar__item has-sub\">
                            <a href=\"#\">
                                <span class=\"bg-link\">Nhà chia sẻ</span>
                            </a>
                           
                        </li>
                        <li class=\"header-navbar__item has-sub\">
                            <a href=\"#\">
                                <span class=\"bg-link\">Hẻm đồ chơi</span>
                            </a>
                           
                        </li>
                        <li class=\"header-navbar__item has-sub\">
                            <a href=\"#\">
                                <span class=\"bg-link\">Phòng kết nối</span>
                            </a>
                           
                        </li>
                        <li class=\"header-navbar__item has-sub\">
                            <a href=\"/app\">
                                <span class=\"bg-link\">Tham gia ngay</span>
                            </a>
                           
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>
<div class=\"modal fade\" id=\"modal-search\" role=\"dialog\">
    <button class=\"close\" type=\"button\" data-dismiss=\"modal\">
        <i class=\"zmdi zmdi-close\"></i>
    </button>
    <div class=\"modal-dialog\">
        <div class=\"modal-content\">
            <div class=\"modal-body\">
                <form class=\"form form--icon\" method=\"POST\">
                    <input type=\"text\" name=\"search\" placeholder=\"Search here...\" />
                    <button class=\"btn-submit-1\" type=\"submit\">
                        <i class=\"fa fa-search\"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<div class=\"header-mobile js-header-mobile d-block d-lg-none\">
    <div class=\"header-mobile__bar\">
        <div class=\"container-fluid clearfix\">
            <a class=\"logo\" href=\"index1.html\">
                <img src=\"{{ 'assets/images/landing/icon/logo.png'|theme }}\" alt=\"Jooby\" />
            </a>
            <button class=\"hamburger hamburger--slider float-right\" type=\"button\">
                <span class=\"hamburger-box\">
                    <span class=\"hamburger-inner\"></span>
                </span>
            </button>
        </div>
    </div>
    <nav class=\"navbar-mobile\">
        <ul class=\"navbar-mobile__list list-unstyled\">
            <li class=\"has-sub\">
                <a href=\"#\">Trang chủ</a>
            </li>
            <li class=\"has-sub\">
                <a href=\"#\">Tulutula là gì?</a>
            </li>
            <li class=\"has-sub\">
                <a href=\"#\">Nhà chia sẻ</a>
            </li>
            <li class=\"has-sub\">
                <a href=\"#\">Hẻm đồ chơi</a>
            </li>
            <li class=\"has-sub\">
                <a href=\"#\">Phòng kết nối</a>
            </li>
            <li class=\"has-sub\">
                <a href=\"#\">Tham gia ngay</a>
            </li>
           
        </ul>
    </nav>
</div>", "/var/www/html/themes/hippo-default-theme/partials/header.htm", "");
    }
    
    public function checkSecurity()
    {
        static $tags = [];
        static $filters = ["theme" => 14];
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
