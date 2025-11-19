<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Student Activity Permit</title>
  <style>
    body {
      font-family: DejaVu Sans, sans-serif;
      margin: 30px;
      font-size: 12pt;
    }

    .header {
      text-align: center;
      margin-bottom: 20px;
    }

    .header img {
      width: 80px;
      height: 80px;
      vertical-align: middle;
    }

    .university-name {
      font-size: 16pt;
      font-weight: bold;
      color: #0047ab;
      margin: 5px 0;
    }

    .subtext {
      font-size: 10pt;
      color: #333;
    }

    h2.title {
      text-align: center;
      text-transform: uppercase;
      margin: 20px 0;
      color: #0047ab;
      font-weight: bold;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }

    table,
    th,
    td {
      border: 1px solid #000;
    }

    th,
    td {
      padding: 5px;
      vertical-align: top;
    }

    .checkbox-group label {
      display: block;
      font-weight: normal;
    }

    .signature-section {
      width: 100%;
      margin-top: 40px;
    }

    .signature-box {
      width: 32%;
      display: inline-block;
      text-align: center;
      vertical-align: top;
    }

    .signature-line {
      border-top: 1px solid #000;
      margin-top: 60px;
      width: 80%;
      margin-left: auto;
      margin-right: auto;
    }

    .signature-label {
      font-size: 10pt;
      margin-top: 5px;
    }

    .footer-note {
      font-size: 9pt;
      margin-top: 20px;
    }

    .date-section {
      margin-top: 30px;
      text-align: right;
      font-size: 10pt;
    }
  </style>
</head>

<body>

  <div class="header">
    <img src="{{ public_path('images/slsu_logo.png') }}" alt="SLSU Logo">
    <div class="university-name">Southern Leyte State University</div>
    <div class="subtext">Main Campus, San Roque, Sogod, Southern Leyte</div>
    <div class="subtext">Email: president@southernleytestateu.edu.ph | Website: www.southernleytestateu.edu.ph</div>
  </div>

  <h2 class="title">Student Activity Permit</h2>

  <table>
    <tr>
      <td><strong>Name of Registered Student / Organization / Subject / Class</strong></td>
      {{-- <td>{{ $student_name }}</td> --}}
      <td>Rovic Kristian</td>
    </tr>
    <tr>
      <td><strong>Date of Filing</strong></td>
      {{-- <td>{{ $date_of_filing }}</td> --}}
      <td>June 10, 2024</td>
    </tr>
    <tr>
      <td><strong>Title of Activity</strong></td>
      {{-- <td>{{ $title }}</td> --}}
      <td>Leadership Training</td>
    </tr>
    <tr>
      <td><strong>Purpose</strong></td>
      {{-- <td>{{ $purpose }}</td> --}}
      <td>To enhance leadership skills among student leaders.</td>
    </tr>
    <tr>
      <td><strong>Type</strong></td>
      <td class="checkbox-group">
        {{-- <label><input type="checkbox" {{ $type_in_campus }}> In-campus</label>
        <label><input type="checkbox" {{ $type_off_campus }}> Off-campus</label> --}}
        <label>Meeting </label>
      </td>
    </tr>
    <tr>
      <td><strong>Nature</strong></td>
      <td class="checkbox-group">
        {{-- <label><input type="checkbox" {{ $nature_training }}> Training / Seminar</label>
        <label><input type="checkbox" {{ $nature_conference }}> Conference / Summit</label>
        <label><input type="checkbox" {{ $nature_culmination }}> Culmination</label>
        <label><input type="checkbox" {{ $nature_socialization }}> Socialization</label>
        <label><input type="checkbox" {{ $nature_meeting }}> Meeting</label>
        <label><input type="checkbox" {{ $nature_concert }}> Concert</label>
        <label><input type="checkbox" {{ $nature_exhibit }}> Exhibit</label>
        <label><input type="checkbox" {{ $nature_program }}> Program</label>
        <label><input type="checkbox" {{ $nature_educational }}> Educational Tour</label>
        <label><input type="checkbox" {{ $nature_clean }}> Clean and Green</label>
        <label><input type="checkbox" {{ $nature_competition }}> Competition</label>
        <label><input type="checkbox" {{ $nature_other }}> Other: {{ $nature_other_text }}</label> --}}
      </td>
    </tr>
    <tr>
      <td><strong>Venue</strong></td>
      {{-- <td>{{ $venue }}</td> --}}
      <td>University Auditorium</td>
    </tr>
    <tr>
      <td><strong>Date</strong></td>
      {{-- <td>{{ $activity_date }}</td> --}}
      <td>June 20, 2024</td>
    </tr>
    <tr>
      <td><strong>Time</strong></td>
      {{-- <td>{{ $activity_time }}</td> --}}
      <td>8:00 AM - 5:00 PM</td>
    </tr>
    <tr>
      <td><strong>Participants</strong></td>
      <td class="checkbox-group">
        {{-- <label><input type="checkbox" {{ $participants_members }}> Members</label>
        <label><input type="checkbox" {{ $participants_officers }}> Officers</label>
        <label><input type="checkbox" {{ $participants_all }}> All Students</label>
        <label><input type="checkbox" {{ $participants_other }}> Other: {{ $participants_other_text }}</label>
        <p><strong>Number:</strong> {{ $participants_number }}</p> --}}
      </td>
    </tr>
  </table>

  <div class="signature-section">
    <div class="signature-box">
      <div class="signature-line"></div>
      <div class="signature-label">Student Organization President / Class President</div>
    </div>

    <div class="signature-box">
      <div class="signature-line"></div>
      <div class="signature-label">Student Organization Adviser / Class Instructor</div>
    </div>

    <div class="signature-box">
      <div class="signature-line"></div>
      <div class="signature-label">Business Affairs and Resource Generation Office Personnel</div>
    </div>
  </div>

  <div class="signature-section">
    <div class="signature-box">
      <div class="signature-line"></div>
      <div class="signature-label">Office of Student Development Services Head (For Main Campus Only)</div>
    </div>

    <div class="signature-box">
      <div class="signature-line"></div>
      <div class="signature-label">Office of Student Affairs and Services Director / Head / College Dean / Institute
        Director</div>
    </div>

    <div class="signature-box">
      <div class="signature-line"></div>
      <div class="signature-label">Vice President for Students and Auxiliary Services / Vice President for Academic
        Affairs / Campus Director</div>
    </div>
  </div>

  <p class="footer-note">
    In accordance with the provisions of the Data Privacy Act of 2012, I hereby grant the Office of Student Development
    Services / Office of Student Affairs and Services the lawful use of my personal information. I further certify that
    the information contained is true and correct.
  </p>

  <p class="footer-note">
    NOTE: This form must be submitted together with the pertinent attachments to the Office of Student Development
    Services (OSDS)
    or Office of Student Affairs and Services (OSAS) three (3) days before the activity. Upon approval, there must be no
    change made in the permit without consent from the OSDS Head or OSAS Head. A change in the schedule and venue due to
    the unprecedented circumstances will be considered then a notification letter must be submitted to the OSDS or OSAS.
  </p>

  <div class="date-section">
    <p>Doc. Code: SLSU-QF-SPS08 | Revision: 04 | Date: 04 August 2023</p>
  </div>

</body>

</html>