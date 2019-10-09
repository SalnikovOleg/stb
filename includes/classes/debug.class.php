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
      // ������� �������� ����� ������ ����� �������,
      // ���� �������� ������� � ����� �� ����� 
      // ���� ������������� ������� (��. ����)
      $this->cur_count++;

      // ������� �������� ������������ ������
      $cur_memory  =  memory_get_usage();

      // �������� ������������ ��������
      if ($cur_memory > $this->max_memory)
      {
         $this->max_memory               =  $cur_memory;
         $this->max_memory_point         =  $this->cur_point;
         $this->max_memory_count         =  $this->cur_count;
      }

      // ����� �������� ������������ �������� �������� ������
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