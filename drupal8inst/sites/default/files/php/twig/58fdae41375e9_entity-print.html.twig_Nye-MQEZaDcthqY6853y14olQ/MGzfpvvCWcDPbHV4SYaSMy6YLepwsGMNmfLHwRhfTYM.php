<?php

/* profiles/oahdefault/modules/contrib/entity_print/templates/entity-print.html.twig */
class __TwigTemplate_edf1d74c1626ee57631fd5d2d62e5245d3af7a64b55c35578c9b99064b002d44 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $tags = array();
        $filters = array();
        $functions = array();

        try {
            $this->env->getExtension('sandbox')->checkSecurity(
                array(),
                array(),
                array()
            );
        } catch (Twig_Sandbox_SecurityError $e) {
            $e->setTemplateFile($this->getTemplateName());

            if ($e instanceof Twig_Sandbox_SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof Twig_Sandbox_SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof Twig_Sandbox_SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

        // line 1
        echo "<html>
<head>
  <meta charset=\"utf-8\">
  <title>";
        // line 4
        echo $this->env->getExtension('sandbox')->ensureToStringAllowed($this->env->getExtension('drupal_core')->escapeFilter($this->env, (isset($context["title"]) ? $context["title"] : null), "html", null, true));
        echo "</title>
  ";
        // line 5
        echo $this->env->getExtension('sandbox')->ensureToStringAllowed($this->env->getExtension('drupal_core')->escapeFilter($this->env, (isset($context["entity_print_css"]) ? $context["entity_print_css"] : null), "html", null, true));
        echo "
</head>
<body>
<div class=\"page\">
    ";
        // line 9
        echo $this->env->getExtension('sandbox')->ensureToStringAllowed($this->env->getExtension('drupal_core')->escapeFilter($this->env, (isset($context["content"]) ? $context["content"] : null), "html", null, true));
        echo "
</div>
</body>
</html>
";
    }

    public function getTemplateName()
    {
        return "profiles/oahdefault/modules/contrib/entity_print/templates/entity-print.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  59 => 9,  52 => 5,  48 => 4,  43 => 1,);
    }

    public function getSource()
    {
        return "<html>
<head>
  <meta charset=\"utf-8\">
  <title>{{ title }}</title>
  {{ entity_print_css }}
</head>
<body>
<div class=\"page\">
    {{ content }}
</div>
</body>
</html>
";
    }
}
