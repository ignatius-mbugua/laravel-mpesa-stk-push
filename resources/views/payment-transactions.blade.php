@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Payment Transactions</div>

                <div class="card-body">
                    @if(count($payment_transactions) > 0)
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">Amount</th>
                                <th scope="col">Receipt Number</th>
                                <th scope="col">Transaction Date</th>
                                <th scope="col">Phone Number</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($payment_transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->amount }}</td>
                                <td>{{ $transaction->receipt_number }}</td>
                                <td>{{ $transaction->transaction_date }}</td>
                                <td>{{ $transaction->phone_number }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <p>No mpesa transactions available</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection