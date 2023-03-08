<h1>Forgot password mail</h1>

<p>Dear {{ $data['recipientName'] }}</p>
<p>{{ $data['textOne'] }}</p>
<p>{{ $data['textTwo'] }}</p>


<a href="{{ $data['buttonLink'] }}" style="display: inline-block; background-color: #007bff; color: #fff; padding: 10px 20px; border-radius: 5px; text-decoration: none;">{{ $data['buttonText'] }}</a>

<p>Thank you for using (company name)!</p>

<p>Best regards,</p>
<p>The Team</p>