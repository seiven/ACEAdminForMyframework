<?php
class Pagination {
	private $total; // 数据表中总记录数
	private $listRows; // 每页显示行数
	private $limit;
	private $uri;
	private $pageNum; // 页数
	private $config = array (
			'header' => "记录",
			"prev" => "上一页",
			"next" => "下一页",
			"first" => "首 页",
			"last" => "尾 页" 
	);
	private $listNum = 8;
	function __construct($total, $listRows = 10, $parameter = "") {
		$this->total = $total;
		$this->listRows = $listRows;
		$this->uri = $this->getUri ( $parameter );
		
		$this->page = ! empty ( $_GET ["page"] ) ? $_GET ["page"] : 1;
		$this->pageNum = ceil ( $this->total / $this->listRows );
		$this->limit = $this->setLimit ();
		// var_dump($this);
	}
	private function setLimit() {
		return "Limit " . ($this->page - 1) * $this->listRows . ",{$this->listRows}";
	}
	private function getUri($parameter) {
		// $url=$_SERVER["REQUEST_URI"].(strpos($_SERVER["REQUEST_URI"],'?')?'':"?").$parameter;
		$url = CRequest::getUri ();
		
		$buPath = isset ( $_SERVER ['HTTP_BUHOST'] ) ? $_SERVER ['HTTP_BUHOST'] : null;
		
		if (! empty ( $buPath )) {
			$url = '/' . $buPath . $url;
		}
		
		$parse = parse_url ( $url );
		
		if (isset ( $parse ["query"] )) {
			parse_str ( $parse ['query'], $params );
			unset ( $params ["page"] );
			if (! empty ( $params )) {
				$url = $parse ['path'] . '?' . http_build_query ( $params );
			} else {
				$url = $parse ['path'];
			}
		}
		
		return $url;
	}
	public function __get($args) {
		if ($args == "limit")
			return $this->limit;
		else
			return null;
	}
	private function start() {
		if ($this->total == 0) {
			return 0;
		} else {
			return ($this->page - 1) * $this->listRows + 1;
		}
	}
	private function end() {
		return min ( $this->page * $this->listRows, $this->total );
	}
	private function first() {
		$html = '';
		if ($this->page == 1) {
			$html .= "<a href='javascript:void(0)'>{$this->config["first"]}</a>";
		} else {
			$html .= "<a href='{$this->uri}" . (false != strpos ( $this->uri, '?' ) ? '&' : '?') . "page=1'>{$this->config["first"]}</a>";
		}
		
		return $html;
	}
	private function prev() {
		$html = '';
		if ($this->page == 1) {
			$html .= "<a href='javascript:void(0)'>{$this->config["prev"]}</a>";
		} else {
			$html .= "<a class='prev' href='{$this->uri}" . (false != strpos ( $this->uri, '?' ) ? '&' : '?') . "page=" . ($this->page - 1) . "'>{$this->config["prev"]}</a>";
		}
		
		return $html;
	}
	private function pageList() {
		$linkPage = "";
		
		$inum = floor ( $this->listNum / 2 );
		
		for($i = $inum; $i >= 1; $i --) {
			$page = $this->page - $i;
			
			if ($page < 1)
				continue;
			
			$linkPage .= "<a href='{$this->uri}" . (false != strpos ( $this->uri, '?' ) ? '&' : '?') . "page={$page}'>{$page}</a>";
		}
		
		$linkPage .= "<a id='PageOn' href='javascript:void(0)'>{$this->page}</a>";
		
		for($i = 1; $i < $inum; $i ++) {
			$page = $this->page + $i;
			if ($page <= $this->pageNum)
				$linkPage .= "<a href='{$this->uri}" . (false != strpos ( $this->uri, '?' ) ? '&' : '?') . "page={$page}'>{$page}</a>";
			else
				break;
		}
		
		return $linkPage;
	}
	private function next() {
		$html = '';
		if ($this->page == $this->pageNum) {
			$html .= "<a href='javascript:void(0)'>{$this->config["next"]}</a>";
		} else {
			$html .= "<a class='prev' href='{$this->uri}" . (false != strpos ( $this->uri, '?' ) ? '&' : '?') . "page=" . ($this->page + 1) . "'>{$this->config["next"]}</a>";
		}
		
		return $html;
	}
	private function last() {
		$html = '';
		if ($this->page == $this->pageNum) {
			$html .= "<a href='javascript:void(0)'>{$this->config["last"]}</a>";
		} else {
			$html .= "<a href='{$this->uri}" . (false != strpos ( $this->uri, '?' ) ? '&' : '?') . "page=" . ($this->pageNum) . "'>{$this->config["last"]}</a>";
		}
		return $html;
	}
	private function goPage() {
		return '<ul class="pr"><input type="text" onkeydown="javascript:if(event.keyCode==13){var page=(this.value>' . $this->pageNum . ')?' . $this->pageNum . ':this.value;location=\'' . $this->uri . '&page=\'+page+\'\'}" value="' . $this->page . '" style="width:25px"><input type="button" value="GO" onclick="javascript:var page=(this.previousSibling.value>' . $this->pageNum . ')?' . $this->pageNum . ':this.previousSibling.value;location=\'' . $this->uri . '&page=\'+page+\'\'"></ul>';
	}
	function fpage($display = array(0,1,2,3,4,5,6,7,8)) {
		if ($this->pageNum <= 1) {
			return '';
		}
		$html [0] = "<div class='page_left'>&nbsp;&nbsp;共有<b>{$this->total}</b>{$this->config['header']}&nbsp;&nbsp;";
		$html [1] = "&nbsp;&nbsp;本页显示<b>" . ($this->end () - $this->start () + 1) . "</b>条,本页<b>{$this->start()}-{$this->end()}</b>条&nbsp;&nbsp;";
		$html [2] = "&nbsp;&nbsp;<b>{$this->page}/{$this->pageNum}</b>页&nbsp;&nbsp;</div>";
		$html [3] = $this->first ();
		$html [4] = $this->prev ();
		$html [5] = $this->pageList ();
		$html [6] = $this->next ();
		$html [7] = $this->last ();
		$html [8] = $this->goPage ();
		$fpage = '';
		foreach ( $display as $index ) {
			$fpage .= $html [$index];
		}
		$fpage .= '';
		
		return $fpage;
	}
}
