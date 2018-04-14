<?php
namespace lib\core;
/**
 * 依赖自动注入
 * Class Di
 * @package lib\core
 */
class Di {

    protected $className;

    protected $methodName;

    protected $args;

    public function __construct($className, $methodName=null){
        $this->className = $className;
        $this->methodName = $methodName;
    }

    /**
     * @param null $className
     * @return mixed|object
     * @throws \ReflectionException
     */
    protected function buildClass($className=null){
        if(is_null($className)){
            $class = new \ReflectionClass($this->className);
        }else{
            $class = new \ReflectionClass($className);
        }
        //获取构造方法
        $constructor = $class->getConstructor();

        if(is_null($constructor)){
            //如果没有构造方法直接返回类的实例
            return $class->newInstance();
        }elseif ($constructor->isPrivate() or $constructor->isProtected()){
            //如果是单例模式则调用instance方法获取实例
            if($class->hasMethod('instance')){
                return call_user_func($className.'::instance');
            }else{
                throw new \Exception('没有找到instance方法');
            }
        }else{
            //获取构造方法的参数
            $parameters = $constructor->getParameters();
            //解析参数
            $dependencies = $this->getDependencies($parameters);
            return $class->newInstanceArgs($dependencies);
        }
    }


    public function runWithArgs($args=null){
        $this->args = (array)$args;

        if(is_null($this->methodName)){
            //没有指定调用哪个方法
            return $this->buildClass();
        }else{
//            //解析要调用的类的方法
            $method = new \ReflectionMethod($this->className, $this->methodName);
            //获取方法的参数
            $parameters = $method->getParameters();
            //解析参数
            $dependencies = $this->getDependencies($parameters);

            return $method->invokeArgs($this->buildClass(), $dependencies);
        }
    }

    /**
     * 递归解析参数获取依赖
     * @param $parameters array
     * @return array
     */
    protected function getDependencies($parameters){
        $dependencies = [];
        foreach($parameters as $parameter){
            //获取参数的类型提示
            $dependency = $parameter->getClass();

            if(is_null($dependency)){
                //如果没有依赖则解析饼传递参数
                $dependencies[] = $this->resolveParameters($parameter);
            }else{
                //有依赖递归分析依赖
                $dependencies[] = $this->buildClass($dependency->name);
            }
        }
        return $dependencies;
    }

    /**
     * 解析参数
     * @param $parameter
     * @return mixed
     * @throws \Exception
     */
    protected function resolveParameters($parameter){
        if(!empty($this->args)){
            //传递的第一个实参赋给第一个需要复制的形参
            $res = array_shift($this->args);
            return $res;
        }elseif($parameter->isDefaultValueAvailable()){
            //如果没有获取到参数则检查是否有默认值并赋默认值
            return $parameter->getDefaultValue();
        }else{
            throw new \Exception('未赋值的参数:'.$parameter->name);
        }
    }
}