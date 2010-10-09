<?php

include_once(dirname(__FILE__).'/FileHandler.php');

abstract class  PlopBaseRotatingHandler
extends         PlopFileHandler
{
    public function emit(PlopRecord &$record)
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
