<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        
        .logo {
            width: 90%;
            margin: 10px 0px 0px 0px;
        }
        
        .header-image{
            width: 15%;
        }

        .header-text{
            width: 85%;
            padding: 15px 0px 0px 15px;
        }
        
        .info {
            width: 100%;
            margin-top: 10px;
        }

        .info td {
            padding: 2px 8px 2px 2px;
            text-align: left;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table th, .table td {
            border: 1px solid #343434;
            padding: 4px;
            text-align: left;
        }

        .tableTotal {
            width: 40%;
            border-collapse: collapse;
            float: right;
        }

        .tableTotal th, .tableTotal td {
            border: 1px solid #343434;
            padding: 4px;
            text-align: left;
        }

        .footer {
            margin: 20px 100px 0px 0px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td class="header-image">
                <img src="images/logo.png" alt="Logo PDP Argamulya" class="logo">
            </td>
            <td class="header-text">
                <h2>PDP ARGAMULYA BOGOR</h2>
                <p>
                    Alamat: 9R5X+868, Pandansari, Ciawi, Bogor Regency, West Java 16720<br>
                    Telp: 0878-7373-041, Email: pdpargamulyabogor@gmail.com<br>
                    Website: pdpargamulyabogor.com
                </p>
            </td>
        </tr>
    </table>

    <hr>

    <table class="info" style="width: 100%; table-layout: fixed; border-collapse: collapse;">
        <tr>
            <td style="width: 20%;"><strong>Nama Pelanggan</strong></td>
            <td style="width: 40%;">: {{ $customer->fullname }}</td>
            <td style="width: 20%;"><strong>Petugas</strong></td>
            <td style="width: 20%;">: {{ $order->check_by ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>No. Telp</strong></td>
            <td>: {{ $customer['phone_number'] }}</td>
            <td><strong>Tanggal Pemesanan</strong></td>
            <td>: {{ $order['order_date'] }}</td>
        </tr>
        <tr>
            <td><strong>Alamat</strong></td>
            <td>: {{ $customer['address'] }}</td>
            <td><strong>No. Pemesanan</strong></td>
            <td>: {{ $order['no_ref_order'] }}</td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td><strong>Pembayaran</strong></td>
            <td>: {{ $payment->rekening->payment_method }}</td>
        </tr>
    </table>
    

    <table class="table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 35%;">Nama Produk</th>
                <th style="width: 10%;">Kategori</th>
                <th style="width: 10%;">Qty</th>
                <th style="width: 20%;">Harga Satuan</th>
                <th style="width: 20%;">Sub Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->orderDetail as $key => $orderDetail)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $orderDetail->product->name_product }}</td>
                    <td>{{ $orderDetail->product->category->name_category }}</td>
                    <td>{{ $orderDetail->quantity }}</td>
                    <td>Rp {{ number_format($orderDetail->product->price, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($orderDetail->sub_total, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="tableTotal">
        <tbody>
            <tr>
                <td colspan="5" style="text-align: right;"><strong>Total</strong></td>
                <td style="width: 50%">Rp {{ number_format($total, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="5" style="text-align: right;"><strong>Biaya Pengiriman</strong></td>
                <td>Rp {{ number_format($shipping['cost'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="5" style="text-align: right;"><strong>Grand Total</strong></td>
                <td><strong>Rp {{ number_format($order['total_amount'], 0, ',', '.') }}</strong></td>
            </tr> 
        </tbody>
    </table>

    <div class="footer">
        <p>
            Catatan: Terima kasih atas pembelian Anda. <br>
            Mohon maaf, domba yang sudah dibeli tidak dapat dikembalikan atau ditukar.
        </p>
    </div>
</body>
</html>
