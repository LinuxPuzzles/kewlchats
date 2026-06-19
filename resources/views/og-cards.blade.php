{{-- TEMP / throwaway — Open Graph (link-preview) card mockups, one per brand, at the
     standard 1200×630. Screenshot each box, resize/export to 1200×630, done. Delete the
     `/_og-cards` route + this file once the images are captured. --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=1280, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>OG card mockups</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:500,600,700,800&display=swap" rel="stylesheet">
    <style>
        :root { color-scheme: light; }
        * { margin: 0; box-sizing: border-box; }
        body { background: #e5e7eb; font-family: system-ui, sans-serif; padding: 40px; }
        .wrap { width: 1200px; margin: 0 auto; }
        .cap { font-size: 15px; color: #475569; margin: 28px 0 10px; font-weight: 600; }
        .cap span { color: #94a3b8; font-weight: 400; }
        .card { width: 1200px; height: 630px; box-sizing: border-box; overflow: hidden; position: relative; }

        /* ---------- KewlChats (base / dark / fuchsia) ---------- */
        .kc {
            padding: 64px;
            background: #0b0b14;
            background-image:
                radial-gradient(900px 520px at 82% -12%, rgba(217,70,239,.38), transparent 60%),
                radial-gradient(760px 520px at -5% 112%, rgba(99,102,241,.20), transparent 60%);
            color: #f1f5f9;
            font-family: 'Figtree', system-ui, sans-serif;
            display: flex; align-items: center; gap: 52px;
        }
        .kc-left { flex: 1 1 auto; min-width: 0; }
        .kc-mark { font-size: 104px; font-weight: 800; letter-spacing: -.045em; line-height: .95; }
        .kc-mark span { color: #e879f9; }
        .kc-tag { font-size: 46px; font-weight: 700; line-height: 1.12; margin-top: 20px; }
        .kc-url { font-size: 28px; font-weight: 800; color: #e879f9; margin-top: 36px; }

        /* messenger sample = the hero */
        .kc-app {
            flex: 0 0 500px; width: 500px; border-radius: 30px; overflow: hidden;
            background: #13131e; border: 1px solid rgba(255,255,255,.09);
            box-shadow: 0 30px 70px rgba(0,0,0,.55);
        }
        .kc-head { display: flex; align-items: center; gap: 14px; padding: 22px 26px; background: rgba(255,255,255,.04); border-bottom: 1px solid rgba(255,255,255,.07); }
        .kc-ava { width: 46px; height: 46px; border-radius: 50%; background: linear-gradient(135deg, #e879f9, #a855f7); flex: 0 0 auto; }
        .kc-room { font-size: 27px; font-weight: 700; color: #f1f5f9; line-height: 1.1; }
        .kc-online { font-size: 19px; color: #34d399; }
        .kc-body { padding: 26px; display: flex; flex-direction: column; gap: 15px; background: #0e0e17; }
        .kc-msg { font-size: 25px; line-height: 1.3; padding: 15px 22px; border-radius: 22px; max-width: 80%; }
        .kc-msg.in { background: rgba(255,255,255,.08); color: #e2e8f0; align-self: flex-start; border-bottom-left-radius: 7px; }
        .kc-msg.out { background: #d946ef; color: #fff; align-self: flex-end; border-bottom-right-radius: 7px; }
        .kc-from { display: block; font-size: 17px; color: #e879f9; font-weight: 700; margin-bottom: 3px; }
        .kc-input { display: flex; align-items: center; gap: 12px; padding: 18px 22px; background: rgba(255,255,255,.04); border-top: 1px solid rgba(255,255,255,.07); }
        .kc-field { flex: 1; height: 44px; border-radius: 22px; background: rgba(255,255,255,.07); }
        .kc-send { width: 44px; height: 44px; border-radius: 50%; background: #d946ef; display: grid; place-items: center; color: #fff; font-size: 20px; flex: 0 0 auto; }

        /* ---------- ready2.im (retro IM window) ---------- */
        .r2 {
            background: linear-gradient(180deg, #1f6fd6 0%, #2e86e0 40%, #6cb6f0 100%);
            font-family: Tahoma, Verdana, Geneva, sans-serif;
            display: flex; align-items: center; justify-content: center;
        }
        .r2::after {
            content: ""; position: absolute; left: 0; right: 0; bottom: 0; height: 42%;
            background: radial-gradient(130% 100% at 50% 100%, #84c95f 0%, #4e9b3a 55%, #3c7e2e 100%);
            clip-path: ellipse(150% 100% at 50% 145%);
        }
        .r2-win {
            position: relative; z-index: 1; width: 920px;
            border: 1px solid #0a3d91; border-radius: 12px; overflow: hidden;
            box-shadow: 0 24px 60px rgba(0,0,0,.45);
        }
        .r2-tb {
            display: flex; align-items: center; gap: 10px; padding: 14px 18px;
            color: #fff; font-weight: 700; font-size: 24px; text-shadow: 0 1px 1px rgba(0,0,0,.45);
            background: linear-gradient(180deg, #4aa3ff 0%, #2b7de9 9%, #1858b8 100%);
        }
        .r2-btns { margin-left: auto; display: flex; gap: 7px; }
        .r2-bb {
            width: 30px; height: 24px; display: grid; place-items: center; border-radius: 4px;
            font-size: 15px; color: #fff; border: 1px solid #1d4e9e;
            background: linear-gradient(180deg, #7fbbff, #2f6fd0);
        }
        .r2-bb.x { border-color: #a32a1a; background: linear-gradient(180deg, #ff9384, #d6452f); }
        .r2-body { background: #fff; padding: 54px 56px 60px; text-align: center; }
        .r2-mark { font-size: 112px; font-weight: 800; font-style: italic; letter-spacing: -.03em; color: #16314a; line-height: 1; }
        .r2-mark span { color: #6bbe1b; }
        .r2-tag { font-size: 36px; color: #33415a; margin-top: 16px; }
        .r2-pill {
            margin-top: 30px; display: inline-flex; align-items: center; gap: 12px; font-size: 24px;
            color: #2b5b9e; background: #eaf2fb; border: 1px solid #c7d6ea; border-radius: 999px; padding: 12px 26px;
        }
        .r2-dot { width: 14px; height: 14px; border-radius: 50%; background: #36c437; box-shadow: inset 0 0 0 1px rgba(0,0,0,.2); }

        /* ---------- ready2.im v2 (brand + retro IM conversation window, like the KewlChats one) ---------- */
        .r2b {
            background: linear-gradient(180deg, #1f6fd6 0%, #2e86e0 40%, #6cb6f0 100%);
            font-family: Tahoma, Verdana, Geneva, sans-serif;
            display: flex; align-items: center; gap: 52px; padding: 64px;
        }
        .r2b::after {
            content: ""; position: absolute; left: 0; right: 0; bottom: 0; height: 42%;
            background: radial-gradient(130% 100% at 50% 100%, #84c95f 0%, #4e9b3a 55%, #3c7e2e 100%);
            clip-path: ellipse(150% 100% at 50% 145%);
        }
        .r2b-left { position: relative; z-index: 1; flex: 1 1 auto; min-width: 0; color: #fff; }
        .r2b-mark { font-size: 100px; font-weight: 800; font-style: italic; letter-spacing: -.03em; line-height: .95; text-shadow: 0 2px 5px rgba(0,0,0,.3); }
        .r2b-mark span { color: #cdff84; }
        .r2b-tag { font-size: 40px; font-weight: 700; line-height: 1.15; margin-top: 18px; color: #eaf4ff; text-shadow: 0 1px 3px rgba(0,0,0,.3); }
        .r2b-tag--big { font-size: 60px; text-align: center; }
        .r2b-pop { color: #cdff84; font-weight: 800; }
        .r2b-url { font-size: 28px; font-weight: 800; margin-top: 34px; color: #fff; text-shadow: 0 1px 3px rgba(0,0,0,.3); }

        .r2b-win {
            position: relative; z-index: 1; flex: 0 0 460px; width: 460px;
            border: 1px solid #0a3d91; border-radius: 12px 12px 6px 6px; overflow: hidden;
            box-shadow: 0 26px 60px rgba(0,0,0,.45);
        }
        .r2b-tb {
            display: flex; align-items: center; gap: 10px; padding: 13px 18px;
            color: #fff; font-weight: 700; font-size: 23px; text-shadow: 0 1px 1px rgba(0,0,0,.45);
            background: linear-gradient(180deg, #4aa3ff 0%, #2b7de9 9%, #1858b8 100%);
        }
        .r2b-btns { margin-left: auto; display: flex; gap: 6px; }
        .r2b-bb { width: 28px; height: 22px; display: grid; place-items: center; border-radius: 4px; font-size: 14px; color: #fff; border: 1px solid #1d4e9e; background: linear-gradient(180deg, #7fbbff, #2f6fd0); }
        .r2b-bb.x { border-color: #a32a1a; background: linear-gradient(180deg, #ff9384, #d6452f); }
        .r2b-body { background: #fff; padding: 26px 28px; }
        .r2b-line { font-size: 26px; line-height: 1.55; color: #1b2a3a; }
        .r2b-line b { font-weight: 800; }
        .r2b-line .me { color: #2b7de9; }
        .r2b-line .her { color: #c026d3; }
        .r2b-typing { font-size: 21px; font-style: italic; color: #8a93a3; margin-top: 4px; }
        .r2b-input { display: flex; align-items: center; gap: 12px; padding: 16px 20px; background: #ece9d8; border-top: 1px solid #c7cdb8; }
        .r2b-field { flex: 1; height: 44px; border-radius: 4px; border: 1px solid #7d94b3; background: #fff; box-shadow: inset 0 1px 2px rgba(0,0,0,.12); }
        .r2b-send { padding: 0 24px; height: 44px; display: grid; place-items: center; font-weight: 700; font-size: 22px; color: #163c0b; border: 1px solid #3d8a00; border-radius: 4px; background: linear-gradient(180deg, #bff277, #6bbe1b); box-shadow: inset 0 1px 0 rgba(255,255,255,.6); }

        /* buddy-list body (reuses the .r2b layout + window chrome) */
        .r2b-list { background: #fff; padding: 22px 26px; }
        .r2b-self { display: flex; align-items: center; gap: 12px; padding-bottom: 14px; border-bottom: 1px solid #cfd8e6; }
        .r2b-self .nm { font-size: 25px; font-weight: 800; color: #16314a; }
        .r2b-self .st { font-size: 19px; color: #64748b; }
        .r2b-grp { font-size: 18px; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; color: #2b5b9e; padding: 16px 2px 6px; }
        .r2b-buddy { display: flex; align-items: center; gap: 13px; padding: 7px 4px; font-size: 25px; color: #1b2a3a; }
        .r2b-bdot { width: 14px; height: 14px; border-radius: 50%; box-shadow: inset 0 0 0 1px rgba(0,0,0,.2); flex: 0 0 auto; }
        .r2b-bdot.on { background: #36c437; }
        .r2b-bdot.away { background: #f1b51c; }
        .r2b-count { margin-left: auto; font-size: 20px; color: #64748b; }
    </style>
</head>
<body>
    <div class="wrap">

        <p class="cap">KewlChats — Open Graph card <span>· screenshot the box below (1200 × 630)</span></p>
        <div class="card kc">
            <div class="kc-left">
                <div class="kc-mark">kewl<span>chats</span></div>
                <div class="kc-tag">Your people are<br>already here.</div>
                <div class="kc-url">kewlchats.net</div>
            </div>
            <div class="kc-app">
                <div class="kc-head">
                    <div class="kc-ava"></div>
                    <div>
                        <div class="kc-room">The Lounge</div>
                        <div class="kc-online">● 1,204 online</div>
                    </div>
                </div>
                <div class="kc-body">
                    <div class="kc-msg in"><span class="kc-from">maya</span>movie night? 🍿</div>
                    <div class="kc-msg out">obviously 😎</div>
                    <div class="kc-msg in"><span class="kc-from">deej</span>omw — grabbing snacks</div>
                </div>
                <div class="kc-input">
                    <div class="kc-field"></div>
                    <div class="kc-send">➤</div>
                </div>
            </div>
        </div>

        <p class="cap">ready2.im (buddy-list window) — Open Graph card <span>· screenshot the box below (1200 × 630)</span></p>
        <div class="card r2">
            <div class="r2-win">
                <div class="r2-tb">
                    <span>🌐 ready2.im — Buddy List</span>
                    <span class="r2-btns"><span class="r2-bb">_</span><span class="r2-bb">▢</span><span class="r2-bb x">✕</span></span>
                </div>
                <div class="r2-body">
                    <div class="r2-mark">ready2<span>.im</span></div>
                    <div class="r2-tag">instant messaging, like it used to be</div>
                    <div class="r2-pill"><span class="r2-dot"></span> the buddy list is back</div>
                </div>
            </div>
        </div>

        <p class="cap">ready2.im (messenger) — Open Graph card <span>· screenshot the box below (1200 × 630)</span></p>
        <div class="card r2b">
            <div class="r2b-left">
                <div class="r2b-mark">ready2<span>.im</span></div>
                <div class="r2b-tag">instant messaging,<br>like it used to be</div>
                <div class="r2b-url">ready2.im</div>
            </div>
            <div class="r2b-win">
                <div class="r2b-tb">
                    <span>💬 maya</span>
                    <span class="r2b-btns"><span class="r2b-bb">_</span><span class="r2b-bb">▢</span><span class="r2b-bb x">✕</span></span>
                </div>
                <div class="r2b-body">
                    <div class="r2b-line"><b class="her">maya:</b> omg you're back?? 🥹</div>
                    <div class="r2b-line"><b class="me">you:</b> always 😎</div>
                    <div class="r2b-line"><b class="her">maya:</b> movie night in the lounge, 5 min 🍿</div>
                    <div class="r2b-typing">maya is typing…</div>
                </div>
                <div class="r2b-input">
                    <div class="r2b-field"></div>
                    <div class="r2b-send">Send</div>
                </div>
            </div>
        </div>

        <p class="cap">ready2.im (buddy list, side) — Open Graph card <span>· screenshot the box below (1200 × 630)</span></p>
        <div class="card r2b">
            <div class="r2b-left">
                <div class="r2b-mark">ready2<span>.im</span></div>
                <div class="r2b-tag r2b-tag--big">The buddy list<br><span class="r2b-pop">is back</span></div>
            </div>
            <div class="r2b-win">
                <div class="r2b-tb">
                    <span>👥 Buddy List</span>
                    <span class="r2b-btns"><span class="r2b-bb">_</span><span class="r2b-bb">▢</span><span class="r2b-bb x">✕</span></span>
                </div>
                <div class="r2b-list">
                    <div class="r2b-self"><span style="font-size:30px">😎</span><span class="nm">you</span><span class="st">▾ Online</span></div>
                    <div class="r2b-grp">▾ Buddies (4)</div>
                    <div class="r2b-buddy"><span class="r2b-bdot on"></span> maya</div>
                    <div class="r2b-buddy"><span class="r2b-bdot on"></span> deej</div>
                    <div class="r2b-buddy"><span class="r2b-bdot away"></span> sam <span class="r2b-count">away</span></div>
                    <div class="r2b-buddy"><span class="r2b-bdot on"></span> jojo</div>
                    <div class="r2b-grp">▾ Rooms</div>
                    <div class="r2b-buddy"><span class="r2b-bdot on"></span> The Lounge <span class="r2b-count">218</span></div>
                    <div class="r2b-buddy"><span class="r2b-bdot on"></span> Game Night <span class="r2b-count">47</span></div>
                </div>
            </div>
        </div>

    </div>
</body>
</html>
