<?php
 require_once 'phpdocx/classes/CreateDocx.inc';

 $docx = new CreateDocxFromTemplate('files/RegPE00/charter ver 3.docx');

 print_r($docx->getTemplateVariables());  

