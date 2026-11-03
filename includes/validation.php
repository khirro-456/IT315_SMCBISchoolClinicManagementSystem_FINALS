<?php defined('APP') or exit;

/* Free-text length check (counts characters, not bytes) */
function max_len($v, $n) { return preg_match('/^.{0,' . (int)$n . '}$/su', (string)$v) === 1; }

/* Letter pairs that almost never occur inside real names or words. Measured on ~27,000 first names and
   surnames, 10,000 common English words and Filipino spellings (jh, mh, bh, zh, cz, iy, uw ...).
   Two of them in one word means keyboard mashing, e.g. "paieghpansgpaegna" has hp, sg and gp.
   Handed to app.js by partials/footer.php, so this is the only copy. */
define('RARE_LETTER_PAIRS', 'bc bf bg bj bk bn bp bq bt bv bw bx bz cb cf cj cp cv cw cx dc dj dk dp dq dx fb fc fd fg fh fj fk fm fn fp fq fv fw fx '
    . 'fz gf gj gk gp gq gv gx gz hc hf hg hh hj hk hp hq hv hx hz iw jb jc jd jf jg jj jk jl jm jn jp jq jr js jt jv jw jx jy '
    . 'jz kb kc kd kf kg kj kp kq kt kv kx kz lj lq lx lz md mg mj mk mq mt mv mw mx mz np nq nx oq pb pd pf pg pj pk pn pq pv '
    . 'pw px pz qa qb qc qd qe qf qg qh qi qj qk ql qm qn qo qp qq qr qs qt qv qw qx qy qz rq rx sg sj sv sx td tg tj tp tq tv '
    . 'tx uj uq uu vb vc vd vf vg vh vj vk vm vn vp vq vs vt vu vv vw vx vz wb wc wd wf wg wj wk wm wp wq wv ww wx wz xb xd xf '
    . 'xg xh xj xk xl xm xn xo xq xr xs xu xv xw xx xz yf yg yj yq yx yy zb zc zd zf zg zj zk zl zn zp zq zs zt zv zw zx');

function rare_pair_count($word) {
    static $rare = null;
    if ($rare === null) $rare = array_flip(explode(' ', RARE_LETTER_PAIRS));
    $n = 0;
    for ($i = 0, $last = strlen($word) - 1; $i < $last; $i++) { if (isset($rare[substr($word, $i, 2)])) $n++; }
    return $n;
}

/* Keyboard mashing such as "bsrngobwljrowbrwjl", "paieghpansgpaegna", "asdfgh", "hahaha" (true = reject).
   $loose: short ALL-CAPS words (BSIT, HTN, COPD) and unit abbreviations (hrs, mmHg) are allowed.
   Same rules in assets/js/app.js -> looksLikeGibberish(). */
function looks_like_gibberish($text, $loose = false) {
    $v = 'aeiouyàáâãäåèéêëìíîïòóôõöùúûüýÿ';
    preg_match_all('/\p{L}+/u', (string)$text, $m);
    foreach ($m[0] as $word) {
        if ($loose && mb_strlen($word) <= 6 && $word === mb_strtoupper($word)) continue;
        $w = mb_strtolower($word);
        if (preg_match('/(\p{L}{1,3})\1\1/u', $w)) return true;                                 /* aaa, hahaha, asdasdasd */
        if (preg_match('/qwer|asdf|sdfg|dfgh|fghj|ghjk|hjkl|zxcv|xcvb|cvbn|vbnm|uiop|poiu|lkjh|fdsa|rewq/', $w)) return true;
        if (preg_match('/[^' . $v . ']{5,}/u', $w)) return true;                                  /* 5+ consonants in a row */
        if (mb_strlen($w) >= ($loose ? 5 : 3) && !preg_match('/[' . $v . ']/u', $w)) return true; /* no vowel at all */
        if (preg_match('/[aeiouàáâãäåèéêëìíîïòóôõöùúûü]{5,}/u', $w)) return true;                /* 5+ vowels in a row */
        if (rare_pair_count($w) >= 2) return true;                                               /* hp, sg, gp ... */
    }
    return false;
}

/* Accounts sign in with a school email only: name@smcbi.edu.ph */
define('SCHOOL_EMAIL_DOMAIN', 'smcbi.edu.ph');
function valid_school_email($v) {
    $v = strtolower(trim($v));
    return strlen($v) <= 80 && preg_match('/^[a-z0-9._%+-]+@' . preg_quote(SCHOOL_EMAIL_DOMAIN, '/') . '$/', $v) === 1;
}

function valid_person_name($v) { return preg_match("/^[\p{L}][\p{L}\s.'-]{1,148}$/u", trim($v)) === 1; }
function valid_optional_person_name($v) { $v = trim($v); return $v === '' || valid_person_name($v); }

/* School/Employee ID: must contain at least one letter AND one number (hyphen allowed) */
function valid_school_or_employee_id($v) {
    return preg_match('/^(?=.*[A-Za-z])(?=.*[0-9])[A-Za-z0-9-]{2,50}$/', trim($v)) === 1;
}
function valid_item_code($v) { return preg_match('/^(?=.*[A-Z])[A-Z0-9-]{3,50}$/', strtoupper(trim($v))) === 1; }

/* Optional 11-digit Philippine mobile number: 09XXXXXXXXX */
function valid_contact_number($v) {
    $v = trim($v);
    return $v === '' || preg_match('/^09[0-9]{9}$/', $v) === 1;
}
function valid_medical_note($v) {
    $v = trim($v);
    return $v === '' || preg_match("/^[\p{L}\p{N}\s,.:;()\/%+'-]{2,1000}$/u", $v) === 1;
}
function valid_clinical_text($v) {
    $v = trim($v);
    return $v === '' || preg_match("/^[\p{L}\p{N}\s,.:;()\/%+'°-]{2,2000}$/u", $v) === 1;
}
function valid_temperature($v) {
    $v = trim($v);
    if ($v === '') return true;
    if (!preg_match('/^[0-9]{2}(\.[0-9])?$/', $v)) return false;
    return (float)$v >= 35.0 && (float)$v <= 42.0;
}
function valid_blood_pressure($v) {
    $v = trim($v);
    if ($v === '') return true;
    if (!preg_match('/^([0-9]{2,3})\/([0-9]{2,3})$/', $v, $m)) return false;
    $s = (int)$m[1]; $d = (int)$m[2];
    return $s >= 70 && $s <= 250 && $d >= 40 && $d <= 150 && $s > $d;
}
function valid_pulse_rate($v) {
    $v = trim($v);
    if ($v === '') return true;
    return preg_match('/^[0-9]{2,3}$/', $v) === 1 && (int)$v >= 30 && (int)$v <= 250;
}
