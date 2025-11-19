<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Report</title>
  <style>
    body {
      font-family: DejaVu Sans, sans-serif;
    }

    h1 {
      color: #1E90FF;
      text-align: center;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    th,
    td {
      border: 1px solid #333;
      padding: 8px;
      text-align: left;
    }
  </style>
</head>

<body>
  <h1>{{ $title }}</h1>
  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Name</th>
        <th>Price</th>
      </tr>
    </thead>
    <tbody>
      @foreach($items as $i => $item)
        <tr>
          <td>{{ $i + 1 }}</td>
          <td>{{ $item['name'] }}</td>
          <td>{{ $item['price'] }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</body>

</html>