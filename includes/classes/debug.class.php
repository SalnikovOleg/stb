<?php
class Debug
{
   function & Debug()
   {
      $this->cur_count  =  0;
      register_tick_function(Array(&$this, 'getMemoryUsage'));
      declare (ticks=1);
   }

   function getMemoryUsage()
   {
      // счетчик позволит найти нужное место скрипта,
      // если виновные функция и класс не имеют 
      // кода инициализации отладки (см. ниже)
      $this->cur_count++;

      // текущее значение максимальной памяти
      $cur_memory  =  memory_get_usage();

      // запомним максимальное значение
      if ($cur_memory > $this->max_memory)
      {
         $this->max_memory               =  $cur_memory;
         $this->max_memory_point         =  $this->cur_point;
         $this->max_memory_count         =  $this->cur_count;
      }

      // также запомним максимальное значение прироста памяти
      $memory_delta  =  $cur_memory - $this->prev_memory;
      if ($memory_delta > $this->max_memory_delta)
      {
         $this->max_memory_delta         =  $memory_delta;
         $this->max_memory_delta_point   =  $this->cur_point;
         $this->max_memory_delta_count   =  $this->cur_count;
      }
      $this->prev_memory  =  $cur_memory;

      return TRUE;
   }

   function shot($name)
   {
      $this->params[$name]  =  $this->cur_count . ' / ' . memory_get_usage() . ' / ' . $this->max_memory;
   }
}
?>