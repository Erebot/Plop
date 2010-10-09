<?php

include_once(dirname(__FILE__).'/FileHandler.php');

abstract class  ErebotLoggingBaseRotatingHandler
extends         ErebotLoggingFileHandler
{
    public function emit(ErebotLoggingRecord &$record)
    {
        try {
            if ($this->shouldRollover($record))
                $this->doRollover();
            parent::emit($record);
        }
        catch (Exception $e) {
            $this->handleError($record, $e);
        }
    }
}

?>
