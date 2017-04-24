<?php

/* profiles/oahdefault/modules/contrib/field_collection/templates/field-collection-item.html.twig */
class __TwigTemplate_fb1625e6f7a54676d623e2b9c4dba61f69e6dd98f06017e353133a69b084b635 extends Twig_Template
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
        $tags = array("set" => 25);
        $filters = array("clean_class" => 27);
        $functions = array();

        try {
            $this->env->getExtension('sandbox')->checkSecurity(
                array('set'),
                array('clean_class'),
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

        // line 25
        $context["classes"] = array(0 => "field-collection-item", 1 => ("field-collection-item--name-" . \Drupal\Component\Utility\Html::getClass($this->getAttribute(        // line 27
(isset($context["field_collection_item"]) ? $context["field_collection_item"] : null), "name", array()))), 2 => ("field-collection-item--view-mode-" . \Drupal\Component\Utility\Html::getClass($this->getAttribute(        // line 28
(isset($context["field_collection_item"]) ? $context["field_collection_item"] : null), "view_mode", array()))));
        // line 31
        echo "<div";
        echo $this->env->getExtension('sandbox')->ensureToStringAllowed($this->env->getExtension('drupal_core')->escapeFilter($this->env, $this->getAttribute((isset($context["attributes"]) ? $context["attributes"] : null), "addClass", array(0 => (isset($context["classes"]) ? $context["classes"] : null)), "method"), "html", null, true));
        echo ">
  <div class=\"content\"";
        // line 32
        echo $this->env->getExtension('sandbox')->ensureToStringAllowed($this->env->getExtension('drupal_core')->escapeFilter($this->env, (isset($context["content_attributes"]) ? $context["content_attributes"] : null), "html", null, true));
        echo ">
    ";
        // line 33
        echo $this->env->getExtension('sandbox')->ensureToStringAllowed($this->env->getExtension('drupal_core')->escapeFilter($this->env, (isset($context["content"]) ? $context["content"] : null), "html", null, true));
        echo "
  </div>
</div>
";
    }

    public function getTemplateName()
    {
        return "profiles/oahdefault/modules/contrib/field_collection/templates/field-collection-item.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  56 => 33,  52 => 32,  47 => 31,  45 => 28,  44 => 27,  43 => 25,);
    }

    public function getSource()
    {
        return "{#
/**
 * @file
 * Default theme implementation for field collection items.
 *
 * Available variables:
 * - content: An array of comment items. Use {{ content }} to print them all,
 *   or print a subset such as {{ content.field_example }}. Use
 *   {% hide(content.field_example) %} to temporarily suppress the printing
 *   of a given element.
 * - title: The (sanitized) field collection item label.
 * - url: Direct url of the current entity if specified.
 * - page: Flag for the full page state.
 * - attributes: HTML attributes for the surrounding element.
 *    Attributes include the 'class' information.
 * - content_attributes: HTML attributes for the content element.
 *
 * @see template_preprocess()
 * @see template_preprocess_entity()
 *
 * @ingroup themeable
 */
#}
{%
  set classes = [
    'field-collection-item',
    'field-collection-item--name-' ~ field_collection_item.name|clean_class,
    'field-collection-item--view-mode-' ~ field_collection_item.view_mode|clean_class,
  ]
%}
<div{{ attributes.addClass(classes) }}>
  <div class=\"content\"{{ content_attributes }}>
    {{ content }}
  </div>
</div>
";
    }
}
