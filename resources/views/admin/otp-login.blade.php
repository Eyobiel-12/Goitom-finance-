<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin - OTP Login</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/js/app.js'])
    <style>
        body{font-family:figtree,system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Cantarell,Noto Sans,sans-serif;background:#f6f7fb}
        .card{max-width:420px;margin:6rem auto;padding:2rem;background:#fff;border-radius:12px;box-shadow:0 10px 25px rgba(2,6,23,.06)}
        .btn{display:inline-flex;align-items:center;justify-content:center;padding:.65rem 1rem;border-radius:.5rem;border:1px solid transparent;background:#111827;color:#fff;font-weight:600}
        .btn[disabled]{opacity:.5;cursor:not-allowed}
        .muted{color:#6b7280;font-size:.9rem}
        input{width:100%;padding:.7rem .9rem;border:1px solid #e5e7eb;border-radius:.5rem}
        .row{display:flex;gap:.75rem}
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const stepEmail = document.getElementById('step-email');
            const stepOtp = document.getElementById('step-otp');
            const emailInput = document.getElementById('email');
            const codeInput = document.getElementById('code');
            const sendBtn = document.getElementById('send');
            const verifyBtn = document.getElementById('verify');
            const resendBtn = document.getElementById('resend');
            const info = document.getElementById('info');
            const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            let countdown = 0, timer;

            function setInfo(t){ info.textContent=t || '' }
            function startTimer(){ countdown=60; resendBtn.disabled=true; timer=setInterval(()=>{ resendBtn.textContent='Opnieuw versturen ('+countdown+'s)'; if(--countdown<=0){ clearInterval(timer); resendBtn.disabled=false; resendBtn.textContent='Opnieuw versturen'; } },1000)}

            sendBtn.addEventListener('click', async () => {
                setInfo('');
                sendBtn.disabled=true;
                const res = await fetch('/admin/otp/send-login', { method:'POST', headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'}, body:new URLSearchParams({email: emailInput.value}) });
                const data = await res.json().catch(()=>({success:false,message:'Fout'}));
                sendBtn.disabled=false;
                if(data.success){ stepEmail.style.display='none'; stepOtp.style.display='block'; startTimer(); setInfo('Code verzonden naar '+emailInput.value); } else { setInfo(data.message || 'Fout'); }
            });

            verifyBtn.addEventListener('click', async () => {
                setInfo('');
                verifyBtn.disabled=true;
                const res = await fetch('/admin/otp/verify-login', { method:'POST', headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'}, body:new URLSearchParams({email: emailInput.value, code: codeInput.value}) });
                const data = await res.json().catch(()=>({success:false,message:'Fout'}));
                verifyBtn.disabled=false;
                if(data.success){ window.location.assign('/admin'); } else { setInfo(data.message || 'Ongeldige code'); }
            });

            resendBtn.addEventListener('click', async () => {
                if(resendBtn.disabled) return;
                const res = await fetch('/admin/otp/send-login', { method:'POST', headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'}, body:new URLSearchParams({email: emailInput.value}) });
                const data = await res.json().catch(()=>({success:false}));
                if(data.success){ startTimer(); setInfo('Code opnieuw verzonden'); }
            });
        });
    </script>
    </head>
<body>
    <div class="card">
        <div style="text-align:center;margin-bottom:1rem">
            <img src="{{ asset('logo.png') }}" alt="Logo" style="height:36px"/>
            <h2 style="margin:.75rem 0 0">Admin OTP Inloggen</h2>
        </div>

        <div id="step-email">
            <label class="muted">E-mailadres</label>
            <input id="email" type="email" placeholder="admin@voorbeeld.nl" required />
            <div style="margin-top:1rem"><button id="send" class="btn" type="button">Verificatiecode versturen</button></div>
        </div>

        <div id="step-otp" style="display:none">
            <label class="muted">Verificatiecode</label>
            <input id="code" type="text" maxlength="6" placeholder="123456" />
            <div class="row" style="margin-top:1rem">
                <button id="verify" class="btn" type="button">Inloggen</button>
                <button id="resend" class="btn" type="button" style="background:#374151">Opnieuw versturen</button>
            </div>
        </div>

        <p id="info" class="muted" style="margin-top:1rem"></p>
    </div>
</body>
</html>


