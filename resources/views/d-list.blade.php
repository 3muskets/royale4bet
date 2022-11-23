@extends('layouts.app')
@section('head')

<script type="text/javascript">

    $(document).ready(function() 
    {
        prepareLocale();
    });

    function doCancel(txnId)
    {
        // console.log(txnId);

        $.ajax({
            url: "/ajax/dw/cancel",
            type: "POST",
            data: {id:txnId},
            success: function(data)
            {
                // console.log(data);
                
                var obj = JSON.parse(data);

                if(obj.status == 1)
                {
                    alert(locale['success']);
                }
                else
                {
                    alert(obj.error);
                }

                window.location.href = "/my_profile/deposit/new?status";
            },
            error: function(){}             
        }); 
    }

    function prepareLocale() 
    {
        locale['info'] = "{!! __('common.modal.info') !!}";
        locale['success'] = "{!! __('common.modal.success') !!}";
        locale['error'] = "{!! __('common.modal.error') !!}";
    }

</script>
<style>
    .card
    {
        border: none;
        background: transparent;
    }
    .card-header
    {
        background: linear-gradient(180deg,#131228,#140133);
    }
    .card-header span
    {
        -webkit-mask: linear-gradient(-60deg,#000 30%,#0005,#000 70%) right/300% 100%;
        animation: shimmer 2.5s infinite;
        font-weight: bold;
    }
    #main-table table
    {
        border-top-left-radius: 5px;
        border-top-right-radius: 5px;
        color: #c9c9c9;
    }
    
    #main-table table th
    {
        background: linear-gradient(180deg,#393854,#131228) !important;
    }

    #main-table table tbody tr:nth-child(even)
    {
        box-shadow: 
                    inset 0px 15px 8px -15px #77a5eb,
                    inset 0px -15px 8px -15px #77a5eb; 
    }

    #main-table table tr th, #main-table table tr td
    {
        padding-left: 10px;
        padding-right: 10px;
    }

    #main-table .btn
    {
        display: flex;
        justify-content: center;
        align-items: center;
        background: #3e3a8e;
        border-radius: 5px;
        border: 1px solid transparent;
        line-height: normal;
        height: auto;
        opacity: 1;
        color: #fff;
    }

    #main-table .btn:hover
    {
        filter: brightness(1.1);
    }

    button:focus
    {
        box-shadow: none !important;
        outline: 0 !important;
    }

    @media only screen and (max-width:604px) 
    {
      /* For mobile phones: */
        .main-container-detail-w1
        {
           width: calc(100vw - 80px);
        }

        .main-container-detail-w2
        {
           width: calc(100vw - 0px);
        }

    }
 
</style>
@endsection


@section('content')
<div class="card">

    <div class="card-header">
        <span>Deposit Status List</span>
    </div>

    <div class="card-body" id="main-table">
        @if(count($data) == 0)
            <div id="notes">{{ __('common.datatable.norecords') }}</div>
        @else
            <div class="table-responsive lt-head-wrap" id="table-responsive2">
            
                <table class="table-resize table-sm mb-2 w-100" style="overflow: hidden">
                    
                    <thead class="lt-head">
                        <tr>
                            <th class="lt-column">ID</th>
                            <th class="lt-column">{{__('app.dw.list.type') }}</th>
                            <th class="lt-column">{{__('app.dw.list.amount') }}</th>
                            <th class="lt-column">{{__('app.dw.list.status') }}</th>
                            <th class="lt-column">{{__('app.dw.list.promo') }}</th>
                            <th class="lt-column">{{__('app.dw.list.created_at') }}</th>
                            <th class="lt-column">{{__('app.dw.list.updated_at') }}</th>
                            <th class="lt-column"></th>
                        </tr>
                    </thead>

                    <tbody class="lt-body">
                    @foreach($data as $d)
                        <tr class="lt-row">
                            <td class="lt-cell">
                                {{ $loop->index+1 }}
                            </td>
                            <td class="lt-cell">
                                {{ $d->pType_text }}
                            </td>
                            <td class="lt-cell">
                                {{ Helper::formatMoney($d->amount) }}
                            </td>
                            <td class="lt-cell">
                                {{ $d->status_text }}
                            </td>
                            <td class="lt-cell">
                                {{ $d->promo_name }}
                            </td>

                            <td class="lt-cell">
                                {{ $d->created_at }}
                            </td>
                            <td class="lt-cell">
                                @if ($d->updated_at)
                                    {{ $d->updated_at }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="lt-cell">
                                @if($d->status =='n') 
                                    <button class="btn btn1" onclick="doCancel('{{ $d->id }}')">{{__('app.dw.list.button.cancel') }}</button>
                                @else
                                    <button disabled class="btn btn1" onclick="doCancel('{{ $d->id }}')">{{__('app.dw.list.button.cancel') }}</button>
                                @endif
                            </td>

                        </tr>
                    @endforeach
                    </tbody>

                </table>
            </div>
        </div>
        @endif
    </div>

</div>
@endsection
