<?php

namespace frontend\modules\gorko_ny\widgets;

use Yii;
use yii\bootstrap\Widget;

class PaginationWidgetWithLinks extends Widget
{

	public $total;
  public $current;
  public $url;

  public function run()
  {
    $buttons = '<div class="items_pagination">';
    if ($this->total > 1){
      if ($this->total > 5){
        if ($this->current <= 3){
          for ($i = 1; $i <= 4; ++$i) {
                $buttons .= $this->renderPageButton($i, '', $i == $this->current ? '_active' : '');           
            }
            $buttons .= $this->renderPageButton($this->total, '_last', '');
        }
        elseif ($this->current >= ($this->total - 2)){
          $buttons .= $this->renderPageButton(1, '_first', '');
          for ($i = $this->total - 3; $i <= $this->total; ++$i) {
                $buttons .= $this->renderPageButton($i, '', $i == $this->current ? '_active' : '');           
            }			        
        }
        else{
          $buttons .= $this->renderPageButton(1, '_first', ''); 
          for ($i = $this->current - 1; $i <= $this->current + 1; ++$i) {
                $buttons .= $this->renderPageButton($i, '', $i == $this->current ? '_active' : '');           
            }
            $buttons .= $this->renderPageButton($this->total, '_last', ''); 
        }
      }
      else{
        for ($i = 1; $i <= $this->total; ++$i) {
              $buttons .= $this->renderPageButton($i, '', $i == $this->current ? '_active' : '');           
          }
      }
      $buttons .= '</div>';
      return $buttons;
    }
    else{
      return '';
    }
  }

  private function renderPageButton($page, $class, $active)
  {
    $href = isset($this->url) ? $this->url : $_SERVER['REQUEST_URI'];
    if (!stripos($href, '?')){
      $href .= '?page=' . $page;
    } else {

      if (!stripos($href, 'page=')){
        $href .= '&page=' . $page;
      }

      $href = preg_replace('/page=(\d+)/', ('page=' . $page), $href);
    }
    return '<a href="' . ($page === 1 ? (explode('?', $href)[0]) : $href) . '" class="items_pagination_item '.$active.' '.$class.'" data-page-id="'.$page.'" data-listing-pagitem></a>';
  }

}
