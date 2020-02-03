<nav aria-label="Page navigation" style="float: right; width: auto;">
    <label class="custom-label-pagination">
        全 <span style="color: #dd4b39;">{{ $page['total'] }}</span> 件
    </label>
    <label class="custom-label-pagination">
        {{ $page['currentPage'] ? $page['currentPage'] : 1 }}/{{ $page['totalPage'] ? $page['totalPage'] : 1}}ページ
    </label>
    <ul class="justify-content-end custom-pagination">
        <li class="page-item @if($page['currentPage'] == 1 || $page['currentPage'] == 0) disabled @endif">
            <span class="page-link" onclick="pageing( 1 )">最初へ</span>
        </li>
        <li class="page-item @if($page['currentPage'] == 1 || $page['currentPage'] == 0) disabled @endif">
            <span class="page-link" onclick="pageing( {{ $page['currentPage'] - 1 }} )" tabindex="-1">前へ</span>
        </li>
        @if($page['currentPage']>5)
            <li class="page-item"><span class="page-link" onclick="pageing(1)">[1]</span></li>
            <li class="disabled"><span>...</span></li>
        @endif
        @php
            $start = $page['currentPage'] - 1;
            if($start<1) {
                $start = 1;
            }
            $pageRun = $start;
        @endphp
        @for($i=$start;$i<$page['currentPage'];$i++)
            <li class="page-item">
                <span class="page-link" onclick="pageing({{ $i }})" @if($i==$page['currentPage']) id="current" @endif>[{{ $i }}]</span>
            </li>
            @php $pageRun=$i; @endphp
        @endfor
        @php
            $end = $page['currentPage'] + 1;
            if($end>$page['totalPage']) {
                $end = $page['totalPage'];
            }
        @endphp
        @for($i=$page['currentPage'];$i<=$end;$i++)
            @if($i < $page['totalPage'])
                <li class="page-item">
                    <span class="page-link" onclick="pageing({{ $i }})" @if($i==$page['currentPage']) id="current" @endif>[{{ $i }}]</span>
                </li>
            @endif
            @php $pageRun=$i; @endphp
        @endfor
        @if($pageRun<$page['totalPage']-1)
            <li class="disabled"><span>...</span></li>
        @endif
        <li class="page-item">
            <span class="page-link" onclick="pageing( {{ $page['totalPage'] }} )" @if($page['totalPage']==$page['currentPage']) id="current" @endif>[{{ $page['totalPage'] ? $page['totalPage'] : 1 }}]</span>
        </li>
        <li class="page-item @if($page['totalPage'] == $page['currentPage']) disabled @endif">
            <span class="page-link" href="#" onclick="pageing( {{ $page['currentPage'] + 1 }} )">次へ</span>
        </li>
        <li class="page-item @if($page['totalPage'] == $page['currentPage']) disabled @endif">
            <span class="page-link" onclick="pageing( {{ $page['totalPage'] }} )">最後へ</span>
        </li>
    </ul>
</nav>