<!DOCTYPE html>

@include('tinymce.scripts')

@extends ('core.layouts.app')

@section('title', 'Stock Issuing')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Stock Issuing</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.stock_issues.partials.stockissue-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        @php
                            $details = [
                                'Date' => dateFormat($stock_issue->date, 'd-M-Y'),
                                'Issue To' => @$stock_issue->employee->full_name,
                                'Issue To Third Party' => @$stock_issue->issue_to_third_party,
                                'Reference No' => $stock_issue->ref_no,
                                'Note' => $stock_issue->note,
                                'Created By' => $stock_issue->user ? $stock_issue->user->fullname : '',
                            ];
                        @endphp
                        @foreach ($details as $key => $val)
                            <tr>
                                <th width="30%">{{ $key }}</th>
                                <td>{{ $val }}</td>
                            </tr>
                        @endforeach
                    </table>

                    <div class="row m-1">
                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                            <p style="font-size: 16px">Approval Status</p>
                        </div>

                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">

                            <div class="row ml-1">
                                @if ($stock_issue->status == 'PENDING')
                                    <div class="round col-6 col-lg-3" style="padding: 8px; color: white; background-color: #BDBDBD; text-align: center;"> Pending </div>
                                @elseif ($stock_issue->status == 'APPROVED')
                                    <div class="round col-6 col-lg-3" style="padding: 8px; color: white; background-color: #81C784; text-align: center;"> Approved </div>
                                @elseif ($stock_issue->status == 'ON HOLD')
                                    <div class="round col-6 col-lg-3" style="padding: 8px; color: white; background-color: #FDD835; text-align: center;"> On Hold </div>
                                @else
                                    <div class="round col-6 col-lg-3" style="padding: 8px; color: white; background-color: #b80000; text-align: center;"> Rejected </div>
                                @endif
                            </div>


                        </div>
                    </div>

                    @php
                        $approver = $stock_issue->approver;
                    @endphp

                    @if($approver)
                        <div class="row m-1">

                            <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                <p style="font-size: 16px">Approval Updated By</p>
                            </div>

                            <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">

                                <h4 style="color: #0b0b0b">
                                    @if($approver)
                                        {{$approver->first_name . " " . $approver->last_name}}
                                    @endif
                                </h4>

                                <label class="mt-1">Approval Note</label>
                                <textarea class="form-control tinyinput-small">{{ $stock_issue->approval_note }}</textarea>


                            </div>
                        </div>
                    @endif


                    @permission('approve-issuance')
                    <div class="row m-1">

                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                            <p style="font-size: 16px"> Approval </p>
                        </div>

                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">

                            <form action="{{route('biller.s-issues-approval', $stock_issue->id)}}" method="PUT">

                                <div class="row">

                                    <div class="col-8 col-lg-4">
                                        <label for="status">Approval Status</label>
                                        <select id="status" name="status" class="form-control">
                                            @php
                                                $statuses = ['APPROVED', 'ON HOLD', 'REJECTED']
                                            @endphp

                                            <option value=""> Select a Status </option>
                                            @foreach($statuses as $st)
                                                <option value="{{ $st }}" @if($stock_issue->status === $st) selected @endif>{{ ucwords(strtolower($st)) }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-12 mt-1">
                                        <label for="approval_note">Note</label><br>
                                        <textarea id="approval_note" name="approval_note" class="tinyinput" rows="4">{{ $stock_issue->approval_note ?? '' }}</textarea>
                                    </div>


                                </div>

                                <div class="row mt-1">
                                    <div class="col-4 col-lg-4 d-flex justify-content-center">
                                        <button type="submit" class="btn btn-large btn-blue mb-1">
                                            <i class="fa fa-check"></i> Change Approval Status
                                        </button>
                                    </div>
                                </div>

                            </form>

                        </div>

                    </div>
                    @endauth

                    <div class="table-responsive">
                        <table class="table table-sm tfr my_stripe_single">
                            <thead>
                                <tr class="bg-gradient-directional-blue white">
                                    <th>#</th>
                                    <th width="25%">Stock Item</th>
                                    <th>Unit</th>
                                    <th>Qty On-Hand</th>
                                    <th>Qty Rem</th>
                                    <th>Issue Qty</th>
                                    <th>Location</th>
                                    <th>Assigned To</th>
                                </tr>
                            </thead>
                            <tbody>   
                                @foreach ($stock_issue->items as $i => $item)
                                    <tr>
                                        <td>{{ $i+1 }}</td>
                                        <td>{{ $item->productvar->name }}</td>
                                        <td>{{ @$item->productvar->product->unit->code }}</td>
                                        <td>{{ +$item->qty_onhand }}</td>
                                        <td>{{ +$item->qty_rem }}</td>
                                        <td>{{ +$item->issue_qty }}</td>
                                        <td>{{ @$item->warehouse->title }}</td>
                                        <td>{{ @$item->assignee->full_name }}</td>
                                    </tr>
                                @endforeach                                
                            </tbody>                
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@section('after-scripts')
    {{ Html::script(mix('js/dataTable.js')) }}
    {{ Html::script('core/app-assets/vendors/js/extensions/moment.min.js') }}
    {{ Html::script('core/app-assets/vendors/js/extensions/fullcalendar.min.js') }}
    {{ Html::script('core/app-assets/vendors/js/extensions/dragula.min.js') }}
    {{ Html::script('core/app-assets/js/scripts/pages/app-todo.js') }}
    {{ Html::script('focus/js/bootstrap-colorpicker.min.js') }}
    {{ Html::script('focus/js/select2.min.js') }}

    <script>

        tinymce.init({
            selector: '.tinyinput-large',
            menubar: '',
            plugins: '',
            toolbar: '',
            height: 280,
            readonly  : true,
        });

        tinymce.init({
            selector: '.tinyinput-small',
            menubar: '',
            plugins: '',
            toolbar: '',
            height: 140,
            readonly  : true,
        });

        tinymce.init({
            selector: '.tinyinput',
            menubar: false,
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link table | align lineheight | checklist numlist bullist indent outdent | removeformat',
            height: 200,
        });




    </script>
@endsection