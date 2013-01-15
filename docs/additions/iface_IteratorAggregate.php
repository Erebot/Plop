<?php

/**
 * \brief
 *      Interface to create an external Iterator.
 *
 * \see
 *      http://php.net/IteratorAggregate
 */
interface IteratorAggregate
extends   Traversable
{
    /**
     * Retrieve an external iterator.
     *
     * \retval Traversable
     *      An instance of an object implementing Iterator or Traversable
     *
     * \see
     *      http://php.net/iteratoraggregate.getiterator.php
     */
    public function getIterator();
}
