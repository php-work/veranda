<?php
namespace Veranda\Model;

use Veranda\Contracts\Model;
use Illuminate\Database\Eloquent\Model as LaravelModel;

abstract class Generated extends LaravelModel implements Model
{
    protected static $autoGenId = true;
    public $incrementing        = false;
    protected $keyType          = 'string';

    public function __construct(array $attributes = [])
    {
        if (static::$autoGenId)
        {
            $this->genId();
        }
        parent::__construct($attributes);
    }

    public static function instance(array $attributes = [])
    {
        return new static($attributes);
    }

    public function genId(): string
    {
        $id = $this->genPrimaryId();

        return $this->setId($id)->{$this->primaryKey};
    }

    protected function genPrimaryId(): string
    {
        return \ver\idgen::sorted62();
    }

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }

    public function setId($id): self
    {
        $this->{$this->primaryKey} = $id;

        return $this;
    }

    public function save(array $options = [])
    {
        if (!$this->id)
        {
            $this->genId();
        }
        $this->confirm();

        return parent::save($options);
    }

    protected function confirm(): self
    {
        // 整体confirm勾子
        $methods = $this->afterConfirm();
        if ($methods && is_array($methods)) foreach ($methods as $method) {
            $call = [$this, $method];
            $call();
        }

        return $this;
    }

    protected function afterConfirm() {}
}
