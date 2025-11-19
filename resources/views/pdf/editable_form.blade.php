<!DOCTYPE html>
<html>

<head>
  <title>Editable PDF Form</title>
  <meta charset="utf-8">
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 50px;
    }

    form {
      max-width: 500px;
      margin: 0 auto;
    }

    label {
      display: block;
      margin-top: 15px;
      font-weight: bold;
    }

    input,
    textarea {
      width: 100%;
      padding: 8px;
      border-radius: 5px;
      border: 1px solid #ccc;
      margin-top: 5px;
    }

    button {
      margin-top: 20px;
      padding: 10px 20px;
      background-color: #007bff;
      border: none;
      color: white;
      border-radius: 5px;
      cursor: pointer;
    }

    button:hover {
      background-color: #0056b3;
    }
  </style>
</head>

<body>

  <h2>Fill Out This Form to Generate Your PDF</h2>

  <form method="POST" action="{{ url('/generate-pdf') }}">
    @csrf
    <label>Full Name:</label>
    <input type="text" name="name" required>

    <label>Email:</label>
    <input type="email" name="email" required>

    <label>Address:</label>
    <textarea name="address" rows="3"></textarea>

    <label>Notes:</label>
    <textarea name="notes" rows="4"></textarea>

    <button type="submit">Generate PDF</button>
  </form>

</body>

</html>