@extends('layouts.project')
@section('page-content')
    @if($pageID !== 0)
        <div class="wz-panel-breadcrumb">
            <ol class="breadcrumb pull-left">
                <li class="breadcrumb-item"><a href="{{ wzRoute('home') }}">首页</a></li>
                @if(!empty($project->catalog))
                    <li class="breadcrumb-item"><a href="{{ wzRoute('home', ['catalog' => $project->catalog->id]) }}">{{ $project->catalog->name }}</a></li>
                @endif
                <li class="breadcrumb-item"><a href="{{ wzRoute('project:home', ['id' => $project->id]) }}">{{ $project->name }}</a></li>
                <li class="breadcrumb-item active">{{ $pageItem->title }}</li>
            </ol>
            <ul class="nav nav-pills pull-right">
                @can('page-edit', $pageItem)
                    <li role="presentation" class="mr-2">
                        <button type="button" data-href="{{ wzRoute('project:doc:edit:show', ['id' => $project->id, 'page_id' => $pageItem->id]) }}" data-toggle="tooltip" title="@lang('common.btn_edit')" class="btn btn-primary bmd-btn-icon">
                            <i class="material-icons">mode_edit</i>
                        </button>
                    </li>
                    <li role="presentation" class="mr-2">
                        <button type="button" data-href="{{ wzRoute('project:doc:attachment', ['id' => $project->id, 'page_id' => $pageItem->id]) }}" data-toggle="tooltip" title="附件" class="btn btn-primary bmd-btn-icon">
                            <span class="fa fa-paperclip"></span>
                        </button>
                    </li>
                @endcan
                @if(!empty($history))
                    <li role="presentation" class="mr-2">
                        <button type="button" wz-doc-compare-submit
                                data-doc1="{{ wzRoute('project:doc:json', ['id' => $project->id, 'page_id' => $pageItem->id]) }}"
                                data-doc2="{{ wzRoute('project:doc:history:json', ['history_id' => $history->id, 'id' => $project->id, 'page_id' => $pageItem->id]) }}"
                                data-toggle="tooltip"
                                title="@lang('common.btn_diff')" class="btn btn-primary  bmd-btn-icon">
                            <i class="material-icons">history</i>
                        </button>
                    </li>
                @endif

                <li role="presentation" class="mr-2">
                    <button type="button" data-href="{{ wzRoute('project:doc:read', ['id' => $project->id, 'page_id' => $pageItem->id ]) }}" data-toggle="tooltip" title="阅读模式" class="btn btn-primary bmd-btn-icon">
                        <span class="fa fa-laptop"></span>
                    </button>
                </li>
                @include('components.page-menus-export', ['project' => $project, 'pageItem' => $pageItem])
                @include('components.page-menus', ['project' => $project, 'pageItem' => $pageItem])
            </ul>
            <div class="clearfix"></div>
        </div>
        <div class="wz-project-main">
            <nav class="wz-page-control clearfix">
                <h1 class="wz-page-title">
                    <i class="fa fa-hashtag" style="color: #909090;" title="文档标题" data-toggle="tooltip"></i>
                    {{ $pageItem->title }}
                    @if($type == 'swagger')
                        <a title="原始Swagger文档" target="_blank" href="{{ route('swagger:doc:json', ['id' => $project->id, 'page_id' => $pageItem->id]) }}" class="fa fa-link"></a>
                    @endif
                </h1>
            </nav>
            @include('components.document-info')
            @include('components.tags')

            @if (!empty($pageItem->sync_url))
                <div class="wz-document-swagger-sync-info wz-panel-limit">
                    文档同步地址：<a href="{{ $pageItem->sync_url }}" target="_blank">{{ $pageItem->sync_url }}</a>，最后同步于 {{ $pageItem->last_sync_at ?? '-' }}
                    @can('page-edit', $pageItem)
                        <a href="#" wz-form-submit data-form="#form-document-sync" data-confirm="执行文档同步后，您将成为最后修改人，确定要执行文档同步吗？" class="ml-2" title="同步文档">
                            <i class="fa fa-refresh" data-toggle="tooltip" title="同步文档"></i>
                            <form id="form-document-sync" method="post" style="display: none;"
                                  action="{{ wzRoute('project:doc:sync-from', ['id' => $pageItem->project_id, 'page_id' => $pageItem->id]) }}">
                                {{ csrf_field() }}
                            </form>
                        </a>
                    @endcan
                </div>
            @endif

            <div class="markdown-body wz-panel-limit {{ $type == 'markdown' ? 'wz-markdown-style-fix' : '' }}" id="markdown-body">
                @if($type === 'markdown')
                    <textarea class="d-none wz-markdown-content">{{ $pageItem->content }}</textarea>
                @endif
                @if($type === 'table')
                    <textarea id="x-spreadsheet-content" class="d-none">{{ processSpreedSheet($pageItem->content) }}</textarea>
                    <div class="wz-spreadsheet">
                        <div id="x-spreadsheet"></div>
                    </div>
                @endif
            </div>

            <div class="text-center wz-panel-limit mt-3 wz-content-end">~ END ~</div>

            @if(count($pageItem->attachments) > 0)
                <div class="wz-attachments wz-panel-limit">
                    <h4>附件</h4>
                    <ol>
                        @foreach($pageItem->attachments as $attachment)
                            <li>
                                <a href="{{ $attachment->path }}">
                                    <span class="fa fa-download"></span>
                                    {{ $attachment->name }}
                                    <span class="wz-attachment-info">
                                【{{ $attachment->user->name }}，
                                {{ $attachment->created_at }}】
                            </span>
                                </a>
                            </li>
                        @endforeach
                    </ol>
                </div>
            @endif
        </div>

    @else
        <div class="wz-panel-breadcrumb">
            <ol class="breadcrumb pull-left">
                <li class="breadcrumb-item"><a href="{{ wzRoute('home') }}">首页</a></li>
                @if(!empty($project->catalog))
                    <li class="breadcrumb-item"><a href="{{ wzRoute('home', ['catalog' => $project->catalog->id]) }}">{{ $project->catalog->name }}</a></li>
                @endif
                <li class="breadcrumb-item active">{{ $project->name }}</li>
            </ol>
            <ul class="nav nav-pills pull-right">
                @include('components.page-menus-batch-export', ['project' => $project])
            </ul>
            <div class="clearfix"></div>
        </div>
        <div class="wz-project-main">
            <h1>{{ $project->name ?? '' }}</h1>

            <p class="wz-document-header wz-panel-limit">@lang('document.document_create_info', ['username' => $project->user->name, 'time' => $project->created_at])</p>
            <p class="wz-panel-limit wz-project-description">{{ $project->description ?? '' }}</p>

            @if (!Auth::guest())
                <div class="wz-recently-log wz-panel-limit">
                    <h4>最近活动</h4>
                    <div id="operation-log-recently"></div>
                </div>
            @endif

            @if($project->groups->count() > 0)
                <div class="wz-group-allowed-list wz-panel-limit">
                    <h4>@lang('project.group_added')</h4>
                    <table class="table">
                        <caption></caption>
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>@lang('project.group_name')</th>
                            <th>@lang('project.group_write_enabled')</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($project->groups as $group)
                            <tr>
                                <th scope="row">{{ $group->id }}</th>
                                <td>{{ $group->name }}</td>
                                <td>{{ $group->projects[0]->pivot->privilege == 1 ? __('common.yes') : __('common.no') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif
@endsection

@includeIf("components.{$type}-show")

@push('page-panel')

    @if(config('wizard.reply_support') && $pageID != 0 && !(Auth::guest() && count($pageItem->comments) === 0))
        <div class="wz-project-main">@include('components.comment')</div>
    @endif

    @include('components.doc-compare-script')
@endpush

@push('script')

<script src="{{ cdn_resource('/assets/vendor/moment-with-locales.min.js') }}"></script>
<script>
    $(function () {
        moment.locale('zh-cn');
        @if(!Auth::guest())
        $.wz.request('get', '{!! wzRoute('operation-log:recently', ['limit' => 'project', 'project_id' => $project->id]) !!}', {}, function (data) {
            if (data.trim() === '') {
                $('.wz-recently-log').addClass('d-none');
            } else {
                $('#operation-log-recently').html(data);
                $('#operation-log-recently .wz-operation-log-time').map(function() {
                    $(this).html(moment($(this).html(), 'YYYY-MM-DD hh:mm:ss').fromNow());
                });
            }
        }, null, 'html');
        @endif
        // 快捷键导出
        var exportBtn = $('#wz-export-trigger');
        document.addEventListener('keydown', function (e) {
            if (e.key === "s" && (e.ctrlKey||e.metaKey)) {
                e.preventDefault();
                if (exportBtn) {
                    exportBtn.trigger('click');
                }
            }
        });
    });
</script>

@endpush
