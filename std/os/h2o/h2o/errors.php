<?php


#	Errors
class H2o_Error extends Exception {}
//class ParseError extends H2o_Error {}     // conflicts with a php7 exception handler, but doesn't seem to be used
class TemplateNotFound extends H2o_Error {}
class TemplateSyntaxError extends H2o_Error {}

?>