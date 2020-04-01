<?php
namespace Veranda\Model\Traits;

use Illuminate\Support\Arr;

trait StatusFlow
{
    protected $_statusChange = [];

    abstract protected function getStatusFlow(): array;

    public function setAttribute($key, $value)
    {
        if (($flows = $this->getStatusFlow()) && array_key_exists($key, $flows))
        {
            [
                'default'   => $default,
                'flow'      => $flow,
            ] = $flows[$key];
            if (!$this->checkStatusFlow($flow, $this->getOriginal($key), $value))
            {
                $this->raiseStatusFlowError($this->getOriginal($key), $value);
            }

            $from   = $this->attributes[$key] ?? $default;
            $to     = $value;
            $this->_statusChange = [$from, $to];
            $this->attributes[$key] = $value;
        }

        parent::setAttribute($key, $value);
    }

    public function getAttribute($key)
    {
        if (($flows = $this->getStatusFlow()) && array_key_exists($key, $flows))
        {
            return Arr::get($this->attributes, $key) === null ?
                $flows[$key]['default'] : $this->attributes[$key];
        }

        return parent::getAttribute($key);
    }

    public function isStatusChange($from, $to): bool
    {
        return $this->_statusChange == [$from, $to];
    }

    protected function checkStatusFlow(array $statusFlow, $orgStatus, $nextStatus)
    {
        if ($orgStatus == $nextStatus)
        {
            return true;
        }

        if (!array_key_exists($nextStatus, $statusFlow))
        {
            $this->raiseStatusFlowNotExist($nextStatus);
        }

        if (is_array($statusFlow[$nextStatus]))
        {
            return in_array($orgStatus, $statusFlow[$nextStatus]);
        }

        if (is_bool($statusFlow[$nextStatus]))
        {
            if ($statusFlow[$nextStatus])
            {
                return true;
            }
            if ($orgStatus === null)
            {
                return true;
            } else {
                return false;
            }
        }

        return $orgStatus == $statusFlow[$nextStatus];
    }

    protected function raiseStatusFlowNotExist($nextStatus)
    {
        throw new \UnexpectedValueException("next status $nextStatus not exist");
    }

    protected function raiseStatusFlowError($currentStatus, $nextStatus)
    {
        \ver\raise('aborts.status_flow_error');
    }
}