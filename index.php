<?php
@ob_end_clean();
@ob_start();
$job = [];

if ($_GET["hex"] != "" && strlen($_GET["hex"]) > "1"){
  @ob_end_clean();
  header('Content-Type: application/json');
  header("Access-Control-Allow-Origin: *");
  $baseData = file_get_contents("UnicodeData.txt");
  $base = $baseData;
  $hex = utf8_decode(strtoupper($_GET["hex"]));
  $pattern = preg_quote($hex, '/');
  $pattern = "/^.*$pattern.*\$/m";
  if(preg_match_all($pattern, $base, $matches)){
    $total = count($matches[0]);
    $family = "";
    foreach($matches[0] as $match){
      $match = explode(";",$match);
      $id = $match[0];
      $name = $match[1];
      $char = html_entity_decode('&#'. hexdec($match[0]).';', 0, 'UTF-8');
      $sru = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
      if ($total <= 10){ $family = file_get_contents("https://".$_SERVER[HTTP_HOST].$sru."?blocks=".$id); }
      $cc = [$id,$name,$char,$family];
      $job[] = $cc;
      }
    echo json_encode($job);
    exit;
  } 
  else{
   echo json_encode("This hex does NOT exist in unicode 10.0");
   exit;
  }
}

if ($_GET["blocks"] != "" && strlen($_GET["blocks"]) > "1"){
  @ob_end_clean();
  header("Access-Control-Allow-Origin: *");  
  $search = utf8_decode(strtoupper($_GET["blocks"]));
  $familySearch = substr($search,0,-2);
  $family = "";
  $lines = file("Blocks.txt");
  foreach($lines as $line){
    if(strpos($line, $familySearch) !== false)
      $family = substr($line,0,-2);
  }
  echo $family;
  exit;
}

?>

<html>
  <style>
    body{background:white;color:#3a3030;min-width:500px;font-size:1.4em} 
    input{background:white;color:black;font-size:0.77em;z-index:3;outline: one;position:fixed} 
    button{position: fixed;top:135px;right:36%;cursor:pointer;z-index:3;font-size:23px}
    #hex{top:135px;width:27.5%;padding-left:5px}
    a, span{cursor:pointer;vertical-align: middle}
    a:hover{background:black;color:white}
    #search{margin:12% 0% 0 36%;width:23%}
    article {word-wrap:break-word;margin:-2% 0 100px 0}
    #main{top: 190px;margin:0 36% 0 36%;max-width:34%;width: 27%;min-width:200px;}
    .unc{font-size:5rem;vertical-align: middle;display:block;text-align:left;transition: font-size 1s,text-align 1s}
    .unn{margin:0px 5px 29px -2px;vertical-align: middle;font-size:1em; font-weight: bold;text-align:center}
    .unr{vertical-align: middle;font-size:0.6em;text-align:right}
    .unp{font-size:0.8em;position: relative;max-width:34%;top:-12px;z-index:-1}
    #view{border: none;border-color: transparent;font-size:5em;width:100%;height:0;min-height:174px;
           padding:0 20% 53px 20%;position:fixed;top:0;left:0;z-index:1;text-align:center}
    .unc:hover{font-size:5.3rem;transition:font-size 1s}
    .unn:hover{text-decoration:underline}
    br {content: " ";display: block;margin:1px 0}
    #explorer {position:fixed;bottom:0;left:-10px;height:7%;width:100%}
    #explorerdiv {position:fixed;bottom:0;left:-10px;height:5%;width:100%;background:white;z-index:2}
    #lkp {z-index: 3;color: black;float: right;font-size: 2.2em;position:fixed;top: 124px;right: 37%;}
    #lkp:hover {border-radius:8px;background:#3a3030;color:white;padding:.1x}
    #suggestR{cursor:pointer;top:0;right:0;position:fixed;width:32%;font-size:11px;z-index:2;direction:ltr;color:#9a7b7b}
    #suggestL{cursor:pointer;top:0;left:0;position:fixed;width:32%;font-size:11px;z-index:2;direction:rtl;color:#9a7b7b} 
    ::-moz-selection {background-color:black;color:#fefefe}
    ::selection {background:black;color:black;color:#fefefe}
  </style>
 <body onload="lookup()">
  <input id=view value=🚍 onchange='lookup()' />
    <form onsubmit='return false' id=search>
      <input id=hex placeholder='Unicode, name, hex...' value='oncoming' />
    </form>
    <p>
      <span id="lkp" onclick='lookup(hex.value)'>🔎</span>    
     <article> <p><br></p>
  <div id=main>
    </div>
    </article>
    <p><br><br><br><br></p>
<script>
function copy(me) {
  view.value=document.getElementById(me).innerText
  lu = document.querySelector('#view').select()
  document.execCommand("copy");
}
function hexToDec(hex) {
   return hex.toLowerCase().split('').reduce( (result, ch) =>
       result * 16 + '0123456789abcdefgh'.indexOf(ch), 0)
}
 
function check(_ૐ, ߐ){
ߐ.innerHTML=ߐ.innerHTML.replace(_ૐ,"<mark>"+_ૐ+"</mark>")
window.setTimeout(Ώ,5000)
}

function Ώ(){
ߐ.innerHTML=ߐ.innerHTML.replace("mark","wbr")
}

function lookup(){
  pushLoc() 
  main.innerHTML = '<tr>'
  function onLoad(event) {
    if (this.status === 200) {
      var hold = JSON.parse(req.responseText)
      for (var j=0;j<hold.length;j++){
          chex = hold[j][0]
          cname = hold[j][1]
          cdec = hexToDec(hold[j][0])
          cfamily = hold[j][3];
          if (cdec == "-1"){
             if (window.location.hash.substring(1, 16) === "%20%F0%9F%93%9F"){explorer.value = hex.value.slice(15);explore();throw "Page mode" }
             main.innerHTML = "<span style='font-size:2em'>🐯</span>No unicode match for this word.<p>Double click to lookup any words.<br> Right click for multiples words.</p>"
             synonyms(hex.value)
             throw "Error!";
          }
          var td = document.createElement("td")
          var span1 = document.createElement("span")
          span1.id = "_"+chex
          span1.classList.add("unc")
          span1.innerHTML = "&#"+cdec+";"
          var span2 = document.createElement("span")
          var span3 = document.createElement("span")
          var span4 = document.createElement("span")
          var br = document.createElement("br")
          span2.classList.add("unn")
          span2.innerHTML = cname+"<br>"
          span3.classList.add("unr")
          span3.innerHTML = "Hex: "+chex+" |  Dec: "+cdec+"<br>"+cfamily; 
          span4.id="_-"+chex
          span4.classList.add("unp")
          span4.innerHTML += "<p><br></p>"
          main.appendChild(td)
          td.appendChild(span1)
          td.appendChild(span2)
          main.appendChild(span3)
          main.appendChild(span4)
         }
  setup()
  }
}
  const req = new XMLHttpRequest()
  req.onload = onLoad
  var search = hex.value
  if (search.length == 1){
    search = hex.value.codePointAt().toString(16)
  }
  if (search.length == 2){
    search = hex.value.codePointAt(0).toString(16)
  }
  req.open("GET","https://cdn.ponyhacks.com/unicode10/?hex="+search,true)
  req.send(null)
}

function ajaxWiki(me,id){
  try {
        let search = me
    if (search != null) {
      let wiki = new XMLHttpRequest()
          wiki.onreadystatechange = function() {
            if (wiki.readyState == XMLHttpRequest.DONE) {
                  console.log(wiki.responseText)
              let wikijson = JSON.parse(wiki.responseText)
              for(var k in wikijson) {
                  let wk = wikijson[k].pages
                  //~ console.log(wk)
                  for(var l in wk) {
                      let wkt = wk[l].title
                      let wkid = wk[l].pageid
                      let wkextract = wk[l].extract
                      console.log(wkt)
                      document.getElementById(id).innerHTML += "<p>"+wkextract /*+"<br><a href='' style='z-index:3' onclick=ajaxId(\'"+wkid+"\',\'"+id+"\')>more</a>" */
                      setTimeout(function(){ check(wkt,id) }, 100)
                  }}
              }}
          wiki.open('GET', 'https://en.wikipedia.org/w/api.php?action=query&prop=extracts&exlimit=max&format=json&exsentences=1&origin=*&exintro=&explaintext=&generator=search&gsrlimit=10&gsrsearch=' + search , true)
          wiki.send(null)
            
      } else {console.log("NOPE")}
    }
  catch (nope) {console.log(nope)}
}
ajaxWiki()
function ajaxId(me,id){
  try {
        let search = me
    if (search != null) {
        let wiki = new XMLHttpRequest()
            wiki.onreadystatechange = function() {
              if (wiki.readyState == XMLHttpRequest.DONE) {
                   console.log(wiki.responseText)
                let wikijson = JSON.parse(wiki.responseText)
                for(var k in wikijson) {
                    let wk = wikijson[k].pages
                    // console.log(wk)
                    for(var l in wk) {
                        let wkt = wk[l].title
                        let wkid = wk[l].pageid
                        let wkextract = wk[l].extract
                        console.log(wkt+" "+wkextract)
                        document.getElementById(id).innerHTML += wkt+"<p>"+wkextract
                  }}
                }}
             wiki.open('GET', 'https://en.wikipedia.org/w/api.php?action=query&format=json&origin=*&prop=info&pageids=' + search + '&inprop=url', true)         

  wiki.send(null)
      } else {console/log("NOPE")}
    }
  catch (nope) {console.log(nope)}
}
</script>

<div id=suggestL>
<a>HUMAN</a> 23 <a>GOING</a> <a>INDEX</a> <a>THUMB</a> <a>SPLIT</a> <a>TOUCH</a> <a>GRASP</a> <a>FLICK</a> <a>WRIST</a> <a>PEAKS</a> <a>FRONT</a> <a>FLOOR</a> <a>EVERY</a> <a>OTHER</a> <a>BLINK</a> <a>TENSE</a> <a>SMILE</a> <a>FROWN</a> <a>MOVES</a> <a>TEETH</a> <a>LIMBS</a> <a>GREEN</a> <a>QUEEN</a> <a>GLOBE</a> <a>SWEET</a> <a>SHIRT</a> <a>STICK</a> <a>HOUSE</a> <a>HANDS</a> <a>WOMAN</a> <a>WOMEN</a> <a>BUNNY</a> <a>BLOND</a> <a>SHAPE</a> <a>SWEAT</a> <a>TREND</a> <a>PHONE</a> <a>MARKS</a> <a>SKULL</a> <a>ANGER</a> <a>TEARS</a> <a>QUILT</a> <a>TRAIN</a> <a>ENTRY</a> <a>QUICK</a> <a>CAPUT</a> <a>HORSE</a> <a>SUPER</a> <a>SHAFT</a> <a>DOING</a> <a>LOTUS</a> <a>RETURN</a> <a>LETTER</a> <a>SQUARE</a> <a>SYSTEM</a> <a>DOUBLE</a> <a>MIDDLE</a> <a>TURNED</a> <a>SCRIPT</a> <a>BARRED</a> <a>LAMBDA</a> <a>DENTAL</a> <a>OGONEK</a> <a>STROKE</a> <a>CLOSED</a> <a>VOICED</a> <a>RHOTIC</a> <a>RAISED</a> <a>ACCENT</a> <a>BRIDGE</a> <a>MACRON</a> <a>EQUALS</a> <a>ALMOST</a> <a>ZIGZAG</a> <a>LUNATE</a> <a>DOTTED</a> <a>CURLED</a> <a>TAILED</a> <a>LITTLE</a> <a>GERESH</a> <a>QARNEY</a> <a>MERKHA</a> <a>MASORA</a> <a>DAGESH</a> <a>QAMATS</a> <a>NUMBER</a> <a>FOURTH</a> <a>POETIC</a> <a>ALAYHE</a> <a>TRIPLE</a> <a>MADDAH</a> <a>HAMZAH</a> <a>HAMZAT</a> <a>BARREE</a> <a>CENTRE</a> <a>FILLED</a> <a>SINDHI</a> <a>SKEWED</a> <a>DALATH</a> <a>PTHAHA</a> <a>ZQAPHA</a> <a>ARABIC</a> <a>RISING</a> <a>SYMBOL</a> <a>CANDRA</a> <a>STRESS</a> <a>KHANDA</a> <a>LENGTH</a> <a>CIRCLE</a> <a>CREDIT</a> <a>POWERS</a> <a>CHILLU</a> <a>SINGLE</a> <a>SPUNGS</a> <a>-KHYUD</a> <a>RNYING</a> <a>SVASTI</a> <a>MEDIAL</a> <a>KHAMTI</a> <a>LABIAL</a> <a>Y-CREE</a> <a>BOTTOM</a> <a>SAYISI</a> <a>R-CREE</a> <a>N-CREE</a> <a>HAGLAZ</a> <a>NAUDIZ</a> <a>PERTHO</a> <a>PEORTH</a> <a>SOWILO</a> <a>MANNAZ</a> <a>LAUKAZ</a> <a>ARLAUG</a> <a>FRANKS</a> <a>CASKET</a> <a>SAMYOK</a> <a>CAMNUC</a> <a>PHNAEK</a> <a>MANCHU</a> <a>BEAVER</a> <a>WIGGLY</a> <a>STRONG</a> <a>TALING</a> <a>ASYURA</a> <a>KEMPUL</a> <a>KEMPLI</a> <a>PAKPAK</a> <a>THYOOM</a> <a>TSHOOK</a> <a>NARROW</a> <a>UDATTA</a> <a>RTHANG</a> <a>KAVYKA</a> <a>SHEQEL</a> <a>UPWARD</a> <a>ITALIC</a> <a>HEADED</a> <a>CORNER</a> <a>PAIRED</a> <a>DASHED</a> <a>SUBSET</a> <a>NORMAL</a> <a>FACTOR</a> <a>SQUISH</a> <a>H-TYPE</a> <a>L-TYPE</a> <a>SCREEN</a> <a>SHORTS</a> <a>MEDIUM</a> <a>ON-OFF</a> <a>DEVICE</a> <a>RECORD</a> <a>DELETE</a> <a>BRANCH</a> <a>AMOUNT</a> <a>ELEVEN</a> <a>TWELVE</a> <a>TWENTY</a> <a>EIGHTH</a> <a>FLORAL</a> <a>SYRIAC</a> <a>TYPE-1</a> <a>TYPE-2</a> <a>TYPE-3</a> <a>TYPE-4</a> <a>TYPE-5</a> <a>TYPE-6</a> <a>TYPE-7</a> <a>LESSER</a> <a>FEMALE</a> <a>BEHIND</a> <a>SAFETY</a> <a>BALLOT</a> <a>CENTER</a> <a>SPOKED</a> <a>CURVED</a> <a>GAPPED</a> <a>BESIDE</a> <a>ENDING</a> <a>JOINED</a> <a>SERIFS</a> <a>AROUND</a> <a>SCHEMA</a> <a>DOMAIN</a> <a>NESTED</a> <a>MEMBER</a> <a>DIVIDE</a> <a>BINARY</a> <a>HOOKED</a> <a>ARROWS</a> <a>COPTIC</a> <a>NUBIAN</a> <a>DIRECT</a> <a>BERBER</a> <a>TUAREG</a> <a>FORKED</a> <a>SECOND</a> <a>SPIRIT</a> <a>TANNED</a> <a>HIDING</a> <a>REPEAT</a> <a>POSTAL</a> <a>GIYEOG</a> <a>YEOLIN</a> <a>GYEONG</a> <a>HEAVEN</a> <a>HANGUL</a> <a>KIYEOK</a> <a>TIKEUT</a> <a>KOREAN</a> <a>THIRTY</a> <a>EIGHTY</a> <a>BITING</a> <a>COMING</a> <a>GENTLE</a> <a>JOYOUS</a> <a>BEFORE</a> <a>BROKEN</a> <a>POETRY</a> <a>OPEN-O</a> <a>SAMEKH</a> <a>TTEHEH</a> <a>DDAHAL</a> <a>GHUNNA</a> <a>UIGHUR</a> <a>KAZAKH</a> <a>SHADDA</a> <a>RASOUL</a> <a>SESAME</a> <a>DOLLAR</a> <a>FATHAH</a> <a>DAMMAH</a> <a>KASRAH</a> <a>VESSEL</a> <a>NINETY</a> <a>WEIGHT</a> <a>LIQUID</a> <a>NAXIAN</a> <a>THIRDS</a> <a>TALENT</a> <a>OUNKIA</a> <a>XESTES</a> <a>ARTABE</a> <a>AROURA</a> <a>GRAMMA</a> <a>PLUMED</a> <a>PERMIC</a> <a>WITHIN</a> <a>TURKIC</a> <a>ORKHON</a> <a>SANDHI</a> <a>KISIM5</a> <a>KASKAL</a> <a>NINDA2</a> <a>ILIMMU</a> <a>SIXTHS</a> <a>BAHAR2</a> <a>KUSHU2</a> <a>SHINDA</a> <a>NGGUOQ</a> <a>TXHEEJ</a> <a>INSIDE</a> <a>PERNIN</a> <a>FORMAT</a> <a>VAREIA</a> <a>OLIGON</a> <a>GORGON</a> <a>OXEIAI</a> <a>PIASMA</a> <a>FTHORA</a> <a>KATAVA</a> <a>MIKRON</a> <a>ETERON</a> <a>SEISMA</a> <a>KLASMA</a> <a>LEIMMA</a> <a>PROTOS</a> <a>TRITOS</a> <a>ISAKIA</a> <a>TELOUS</a> <a>CHROMA</a> <a>FHTORA</a> <a>YFESIS</a> <a>DIESIS</a> <a>GENIKI</a> <a>ARSEOS</a> <a>BREATH</a> <a>OTTAVA</a> <a>COMMON</a> <a>DEGREE</a> <a>MINIMA</a> <a>BREVIS</a> <a>TEMPUS</a> <a>KIEVAN</a> <a>HINGED</a> <a>SPREAD</a> <a>CUPPED</a> <a>ANGLED</a> <a>RIPPLE</a> <a>OTHERS</a> <a>STRIKE</a> <a>FINGER</a> <a>SPIRAL</a> <a>DREAMY</a> <a>CHEEKS</a> <a>TONGUE</a> <a>LOOPED</a> <a>KNIGHT</a> <a>BOTTLE</a> <a>HOCKEY</a> <a>TENNIS</a> <a>PADDLE</a> <a>CAPPED</a> <a>TELLER</a> <a>POINTS</a> <a>FACING</a> <a>MOBILE</a> <a>ORANGE</a> <a>POMMEE</a> <a>THUMBS</a> <a>FLOPPY</a> <a>BUTTON</a> <a>SPEECH</a> <a>CRYING</a> <a>BOWING</a> <a>PERSON</a> <a>FOLDED</a> <a>BULLET</a> <a>POLICE</a> <a>LITTER</a> <a>CROCUS</a> <a>COPPER</a> <a>SHADED</a> <a>FORMEE</a> <a>COWBOY</a> <a>STEAMY</a> <a>CONTROL</a> <a>CAPITAL</a> <a>GUARDED</a> <a>PROGRAM</a> <a>ORDINAL</a> <a>DOTLESS</a> <a>AFRICAN</a> <a>PALATAL</a> <a>GLOTTAL</a> <a>LATERAL</a> <a>EPSILON</a> <a>DIGRAPH</a> <a>CENTRED</a> <a>SOLIDUS</a> <a>SEAGULL</a> <a>UPWARDS</a> <a>ARCHAIC</a> <a>NUMERAL</a> <a>SPACING</a> <a>OMICRON</a> <a>UPSILON</a> <a>IZHITSA</a> <a>HUNDRED</a> <a>BASHKIR</a> <a>TELISHA</a> <a>YIDDISH</a> <a>ALLAHOU</a> <a>PERCENT</a> <a>DECIMAL</a> <a>POINTED</a> <a>KNOTTED</a> <a>MARBUTA</a> <a>KIRGHIZ</a> <a>INITIAL</a> <a>ROUNDED</a> <a>UPRIGHT</a> <a>PERSIAN</a> <a>OBLIQUE</a> <a>SOGDIAN</a> <a>MELODIC</a> <a>VOCALIC</a> <a>MARWARI</a> <a>KANTAJA</a> <a>SANYAKA</a> <a>TAALUJA</a> <a>DANTAJA</a> <a>KOMBUVA</a> <a>CLOSING</a> <a>LEADING</a> <a>WESTERN</a> <a>EASTERN</a> <a>COUNCIL</a> <a>PALAUNG</a> <a>SECTION</a> <a>PREFACE</a> <a>CARRIER</a> <a>NASKAPI</a> <a>NUNAVIK</a> <a>NUNAVUT</a> <a>TH-CREE</a> <a>AIVILIK</a> <a>FEATHER</a> <a>BJARKAN</a> <a>OTHALAN</a> <a>VISARGA</a> <a>OJIBWAY</a> <a>DAP-PII</a> <a>DAP-BEI</a> <a>ROTATED</a> <a>DOUBLED</a> <a>MUSICAL</a> <a>GAAHLAA</a> <a>MIDLINE</a> <a>KATHAKA</a> <a>SVARITA</a> <a>INSULAR</a> <a>CEDILLA</a> <a>BRACKET</a> <a>PILCROW</a> <a>ALIGNED</a> <a>OPENING</a> <a>HARPOON</a> <a>REVERSE</a> <a>ANNUITY</a> <a>ELEMENT</a> <a>CONTAIN</a> <a>CONTOUR</a> <a>GREATER</a> <a>LOGICAL</a> <a>PRECEDE</a> <a>SUCCEED</a> <a>BETWEEN</a> <a>DIAMOND</a> <a>CURRENT</a> <a>FLOWING</a> <a>ACCOUNT</a> <a>FIFTEEN</a> <a>SIXTEEN</a> <a>CIRCLED</a> <a>QUARTER</a> <a>EIGHTHS</a> <a>INVERSE</a> <a>SMILING</a> <a>NATURAL</a> <a>GENERIC</a> <a>VOLTAGE</a> <a>WITHOUT</a> <a>ONE-WAY</a> <a>TWO-WAY</a> <a>CHEVRON</a> <a>SQUARED</a> <a>FALLING</a> <a>DIVIDED</a> <a>PATTERN</a> <a>CURVING</a> <a>THROUGH</a> <a>BINDING</a> <a>VARIANT</a> <a>STROKES</a> <a>NEGATED</a> <a>SLANTED</a> <a>AVERAGE</a> <a>PRODUCT</a> <a>SIMILAR</a> <a>SPIDERY</a> <a>IOTATED</a> <a>CROSSED</a> <a>ACADEMY</a> <a>AHAGGAR</a> <a>STACKED</a> <a>RADICAL</a> <a>NGIEUNG</a> <a>LINKING</a> <a>CHIEUCH</a> <a>KHIEUKH</a> <a>THIEUTH</a> <a>PHIEUPH</a> <a>SEVENTY</a> <a>HOLDING</a> <a>ABYSMAL</a> <a>PUSHING</a> <a>KEEPING</a> <a>NEUTRAL</a> <a>BLENDED</a> <a>CHINESE</a> <a>VOLAPUK</a> <a>STIRRUP</a> <a>Private</a> <a>TCHEHEH</a> <a>MAKSURA</a> <a>KORANIC</a> <a>TATWEEL</a> <a>SHADDAH</a> <a>ISOLATE</a> <a>WHEELED</a> <a>CHARIOT</a> <a>MEASURE</a> <a>HERAEUM</a> <a>DELPHIC</a> <a>DRACHMA</a> <a>KYATHOS</a> <a>SEXTANS</a> <a>SEXTULA</a> <a>DIMIDIA</a> <a>SILIQUA</a> <a>ARAMAIC</a> <a>CURSIVE</a> <a>ARABIAN</a> <a>NUMERIC</a> <a>PAHLAVI</a> <a>YENISEI</a> <a>SOMPENG</a> <a>GRANTHA</a> <a>TRIDENT</a> <a>CIRCLES</a> <a>GESHTIN</a> <a>SHESHIG</a> <a>ELAMITE</a> <a>LAK-079</a> <a>LAK-081</a> <a>LAK-449</a> <a>LAK-617</a> <a>LAK-648</a> <a>PHASE-A</a> <a>PHASE-B</a> <a>NGKINDI</a> <a>PHASE-C</a> <a>MKPARAQ</a> <a>NGGUAEN</a> <a>PHASE-D</a> <a>PHASE-E</a> <a>PHASE-F</a> <a>LOW-MID</a> <a>TANGENT</a> <a>CHINOOK</a> <a>KENTIMA</a> <a>STAVROS</a> <a>VAREIAI</a> <a>SYNAGMA</a> <a>KRATIMA</a> <a>IMISEOS</a> <a>LEGETOS</a> <a>PLAGIOS</a> <a>ENARXIS</a> <a>SKLIRON</a> <a>MALAKON</a> <a>TESSERA</a> <a>THESEOS</a> <a>ARKTIKO</a> <a>FERMATA</a> <a>CLUSTER</a> <a>EARTHLY</a> <a>FRAKTUR</a> <a>PARTIAL</a> <a>FINGERS</a> <a>TOUCHES</a> <a>FORWARD</a> <a>SURFACE</a> <a>SQUEEZE</a> <a>TOWARDS</a> <a>HITTING</a> <a>CEILING</a> <a>SHAKING</a> <a>DYNAMIC</a> <a>BLOWING</a> <a>SUCKING</a> <a>PRESSED</a> <a>LICKING</a> <a>AGAINST</a> <a>TILTING</a> <a>KIKAKUI</a> <a>UNIFIED</a> <a>GIBBOUS</a> <a>POPPING</a> <a>VIEWING</a> <a>PLAYING</a> <a>RACQUET</a> <a>ADDRESS</a> <a>MAILBOX</a> <a>LOWERED</a> <a>SPEAKER</a> <a>STAMPED</a> <a>WRITING</a> <a>VICTORY</a> <a>THOUGHT</a> <a>WINKING</a> <a>MEDICAL</a> <a>ROLLING</a> <a>RAISING</a> <a>POUTING</a> <a>CHECKER</a> <a>TRAFFIC</a> <a>SMOKING</a> <a>MERCURY</a>
<a>TOURNOIS</a> <a>CONSTANT</a> <a>SQUIGGLE</a> <a>PEDESTAL</a> <a>CONTAINS</a> <a>PARALLEL</a> <a>ACTUALLY</a> <a>SUPERSET</a> <a>ORIGINAL</a> <a>DIVISION</a> <a>SUBGROUP</a> 
<a>NOTATION</a> <a>INTEREST</a> <a>TRIANGLE</a> <a>TORTOISE</a> <a>EXPONENT</a> <a>CARRIAGE</a> <a>NEGATIVE</a> <a>CUSTOMER</a> <a>THIRTEEN</a> <a>FOURTEEN</a> <a>EIGHTEEN</a> <a>NINETEEN</a> <a>DRAWINGS</a>
<a>JUDEO-SPANISH</a> <a>CLUSTER-FINAL</a> <a>LEFT-TO-RIGHT</a> <a>PARESTIGMENON</a> <a>THIRTY-SECOND</a> <a>TWENTY-EIGHTH</a> <a>DEFECTIVENESS</a> <a>DOUBLE-STRUCK</a> <a>HAND-CURLICUE</a> <a>THREE-QUARTER</a> <a>BOTTOM-SHADE</a>
<a>STRATUM</a> <a>LOZENGE</a> <a>NOTCHED</a> <a>EYEBROW</a> <a>SYMBOLS</a> <a>SELECTED</a> <a>TRANSMIT</a> <a>SEQUENCE</a> <a>VERTICAL</a> <a>POINTING</a> <a>FRACTION</a> <a>QUESTION</a> <a>LIGATURE</a> <a>PRECEDED</a> <a>REVERSED</a> <a>INVERTED</a> <a>ALVEOLAR</a> <a>DIAGONAL</a> <a>FISHHOOK</a> <a>BILABIAL</a> <a>BIDENTAL</a> <a>CENTERED</a> <a>GRAPHEME</a> <a>ASTERISK</a> <a>IOTIFIED</a> <a>CYRILLIC</a> <a>MILLIONS</a> <a>SEMISOFT</a> <a>STRAIGHT</a> <a>MODIFIER</a> <a>EMPHASIS</a> <a>ARMENIAN</a> <a>ETERNITY</a> <a>FOOTNOTE</a> <a>THOUSAND</a> <a>KASHMIRI</a> <a>MARBUTAH</a> <a>ISOLATED</a> <a>HARKLEAN</a> <a>FEMININE</a> <a>EXTENDED</a> <a>OVERLONG</a> <a>ROHINGYA</a> <a>DISPUTED</a> <a>SIDEWAYS</a> <a>CURRENCY</a> <a>ANUSVARA</a> <a>CIRCULAR</a> <a>SANYOOGA</a> <a>SYLLABLE</a> <a>LOGOTYPE</a> <a>LENITION</a> <a>TRAILING</a> <a>EMPHATIC</a> <a>GEORGIAN</a> <a>CHOSEONG</a> <a>THURISAZ</a> <a>BERKANAN</a> <a>MULTIPLE</a> <a>TVIMADUR</a> <a>BELGTHOR</a> <a>INHERENT</a> 
<a>BOUNDARY</a> <a>SELECTOR</a> <a>PRAM-PII</a> <a>PRAM-BEI</a> <a>DAP-MUOY</a> <a>DAP-BUON</a> <a>DAP-PRAM</a> <a>PASANGAN</a> <a>SOUTHERN</a> <a>NORTHERN</a> <a>RIGVEDIC</a> <a>ANUDATTA</a>

</div>
<div id=suggestR>
 <a>QUARTERS</a> <a>QUADRANT</a> <a>FROWNING</a> <a>DRAUGHTS</a> <a>CROSSING</a> <a>LOCATION</a> <a>OUTLINED</a> <a>PINWHEEL</a> <a>PETALLED</a> <a>SHADOWED</a> <a>S-SHAPED</a> <a>DIRECTLY</a> <a>OPERATOR</a> <a>INTEGRAL</a> <a>INTERIOR</a> <a>NEGATION</a> <a>U-SHAPED</a> <a>CIRCLING</a> <a>POSITION</a> <a>LATINATE</a> <a>TAILLESS</a> <a>AKHMIMIC</a> <a>L-SHAPED</a> <a>SPIRITUS</a> <a>BOHAIRIC</a> <a>INDIRECT</a> <a>OMISSION</a> <a>SURROUND</a> <a>STANDARD</a> <a>ENTERING</a> <a>KATAKANA</a> <a>CREATIVE</a> <a>YOUTHFUL</a> <a>CLINGING</a> <a>AROUSING</a> <a>MARRYING</a> <a>SQUIRREL</a> <a>QUANTITY</a> <a>LOGOGRAM</a> <a>BASELINE</a> <a>DAMMATAN</a> <a>KASRATAN</a> <a>FATHATAN</a> <a>MOHAMMAD</a> <a>WASALLAM</a> <a>IDEOGRAM</a> <a>MONOGRAM</a> <a>THESPIAN</a> <a>CYRENAIC</a> <a>STRATIAN</a> <a>METRETES</a> <a>TRYBLION</a> <a>SINUSOID</a> <a>SEMUNCIA</a> <a>DENARIUS</a> <a>TATTOOED</a> <a>ALBANIAN</a> <a>CITATION</a> <a>CRESCENT</a> <a>PARTHIAN</a> <a>SEPTUPLE</a> <a>LIGATING</a> <a>TERMINAL</a> <a>HUNDREDS</a> <a>OPPOSING</a> <a>ASSYRIAN</a> <a>HIGH-LOW</a> <a>REFORMED</a> <a>ROMANIAN</a> <a>ATTACHED</a> <a>APODERMA</a> <a>TROMIKON</a> <a>CHOREVMA</a> <a>KONTEVMA</a> <a>PELASTON</a> <a>TESSARON</a> <a>DIGORGON</a> <a>ARISTERA</a> <a>MARTYRIA</a> <a>DEYTEROS</a> <a>TETARTOS</a> <a>ARCHAION</a> <a>DEYTEROU</a> <a>DIATONON</a> <a>DIASTOLI</a> <a>SIMANSIS</a> <a>DIGRAMMA</a> <a>REPEATED</a> <a>ONE-LINE</a> <a>TWO-LINE</a> <a>SIX-LINE</a> <a>NOTEHEAD</a> <a>FINGERED</a> <a>ORNAMENT</a> <a>PERFECTA</a> <a>TORCULUS</a> <a>HEAVENLY</a> <a>VASTNESS</a> <a>HAND-CUP</a> <a>TOUCHING</a> <a>MOVEMENT</a> <a>EYEBROWS</a> <a>FOREHEAD</a> <a>WIDENING</a> <a>WRINKLES</a> <a>STICKING</a> <a>SHOULDER</a> <a>ROTATION</a> <a>GEMINATE</a> <a>HIRAGANA</a> <a>KEYBOARD</a> <a>BACKHAND</a> <a>BUSINESS</a> <a>RECEIVER</a> <a>FOUNTAIN</a> <a>PERSONAL</a> <a>CALENDAR</a> <a>THROWING</a> <a>RELIEVED</a> <a>MILITARY</a> <a>ANTIMONY</a> <a>POWDERED</a> <a>SLIGHTLY</a> <a>COVERING</a> <a>SEPARATOR</a> <a>PERMITTED</a> <a>CHARACTER</a> <a>QUOTATION</a> <a>RETROFLEX</a> <a>DIAERESIS</a> <a>STRETCHED</a> <a>EXTRA-LOW</a> <a>DEPARTING</a> <a>DIALYTIKA</a> <a>ARROWHEAD</a> <a>UKRAINIAN</a> <a>THOUSANDS</a> <a>ABKHASIAN</a> <a>SUBSCRIPT</a> <a>SUBLINEAR</a> <a>DOWNWARDS</a> <a>COMBINING</a> <a>MALAYALAM</a> <a>NUMERATOR</a> <a>THREE-DOT</a> <a>MUURDHAJA</a> <a>SEMIVOWEL</a> <a>TRUNCATED</a> <a>DELIMITER</a> <a>HONORIFIC</a> <a>SUBJOINED</a> <a>CONSONANT</a> <a>PARAGRAPH</a> <a>JUNGSEONG</a> <a>JONGSEONG</a> <a>SEBATBEIT</a> <a>SYLLABICS</a> <a>WEST-CREE</a> <a>BLACKFOOT</a> <a>VARIATION</a> <a>PRAM-MUOY</a> <a>PRAM-BUON</a> <a>KHUEN-LUE</a> <a>LEFT-HAND</a> <a>UNBLENDED</a> <a>LARYNGEAL</a> <a>FLATTENED</a> <a>INSERTION</a> <a>LEFTWARDS</a> <a>ASTERISKS</a> <a>SYMMETRIC</a> <a>CLOCKWISE</a> <a>ENCLOSING</a> <a>RECORDING</a> <a>SAMARITAN</a> <a>IDENTICAL</a> <a>LESS-THAN</a> <a>CONJUGATE</a> <a>RECTANGLE</a> <a>BACKSLASH</a> <a>SEMICOLON</a> <a>UNDERLINE</a> <a>NORTHWEST</a> <a>SEVENTEEN</a> <a>QUADRUPLE</a> <a>BISECTING</a> <a>SIXTEENTH</a> <a>RECYCLING</a> <a>UNIVERSAL</a> <a>GRAVEYARD</a> <a>PROPELLER</a> <a>PRECEDING</a> <a>CONTOURED</a> <a>INCLUDING</a> <a>DIALECT-P</a> <a>MONOGRAPH</a> <a>ITERATION</a> <a>TELEGRAPH</a> <a>PROLONGED</a> <a>IDEOGRAPH</a> <a>LIABILITY</a> <a>Ideograph</a> <a>Extension</a> <a>RECEPTIVE</a> <a>SPLITTING</a> <a>DARKENING</a> <a>GATHERING</a> <a>MONOCULAR</a> <a>BINOCULAR</a> <a>LEFT-STEM</a> <a>CUATRILLO</a> <a>ALTERNATE</a> <a>VOICELESS</a> <a>ASPIRATED</a> <a>Syllable</a> <a></a> <a>BISMILLAH</a> <a>AR-RAHMAN</a> <a>MESSENIAN</a> <a>CARYSTIAN</a> <a>INDICTION</a> <a>QUINARIUS</a> <a>DUPONDIUS</a> <a>CENTURIAL</a> <a>CARPENTRY</a> <a>CRUCIFORM</a> <a>HUNGARIAN</a> <a>RUDIMENTA</a> <a>MID-LEVEL</a> <a>YPOKRISIS</a> <a>KENTIMATA</a> <a>OYRANISMA</a> <a>SYNDESMOS</a> <a>PSIFISTON</a> <a>FANEROSIS</a> <a>DIATONIKI</a> <a>DIGRAMMOS</a> <a>DIFTOGGOS</a> <a>FOUR-LINE</a> <a>FIVE-LINE</a> <a>GLISSANDO</a> <a>PERFECTUM</a> <a>GREGORIAN</a> <a>PORRECTUS</a> <a>SCANDICUS</a> <a>BRANCHING</a> <a>MONOSPACE</a> <a>HAND-FIST</a> <a>HAND-OVAL</a> <a>CONJOINED</a> <a>HAND-FLAT</a> <a>HAND-CLAW</a> <a>HAND-HOOK</a> <a>DIRECTION</a> <a>EYELASHES</a> <a>WALLPLANE</a> <a>BRACKETED</a> <a>INDICATOR</a> <a>ASCENDING</a> <a>TELEPHONE</a> <a>TOUCHTONE</a> <a>BALLPOINT</a> <a>NETWORKED</a> <a>SAVOURING</a> <a>DELICIOUS</a> <a>STUCK-OUT</a> <a>SCREAMING</a> <a>AMPERSAND</a> <a>REVOLVING</a> <a>SUBLIMATE</a> <a>ISOSCELES</a> <a>EXPLODING</a> <a>TABULATION</a> <a>APOSTROPHE</a> <a>PHARYNGEAL</a> <a>CIRCUMFLEX</a> <a>TRIANGULAR</a> <a>EXTRA-HIGH</a> <a>HOMOTHETIC</a> <a>RIGHTWARDS</a> <a>PAMPHYLIAN</a> <a>KHAKASSIAN</a> <a>HORIZONTAL</a> <a>VERTICALLY</a> <a>DESCENDING</a> <a>EPENTHETIC</a> <a>GEMINATION</a> <a>TWO-CIRCLE</a> <a>ALPAPRAANA</a> <a>FIXED-FORM</a> <a>ATHAPASCAN</a> <a>MOOSE-CREE</a> <a>BIBLE-CREE</a> <a>WOODS-CREE</a> <a>RIGHT-HAND</a> <a>SIMALUNGUN</a> <a>MANDAILING</a> <a>MU-GAAHLAA</a> <a>YAJURVEDIC</a> <a>AGGRAVATED</a> <a>SUSPENSION</a> <a>SANS-SERIF</a> <a>SEMICIRCLE</a> <a>EQUIVALENT</a> <a>SEMIDIRECT</a> <a>FUNCTIONAL</a> <a>SUBSTITUTE</a> <a>CONTAINING</a> <a>ORTHOGONAL</a> <a>CROSSHATCH</a> <a>CONVERGING</a> <a>INTERLACED</a> <a>TRIFOLIATE</a> <a>TWO-HEADED</a> <a>RELATIONAL</a> <a>TROKUTASTI</a> <a>TAWELLEMET</a> <a>PARAPHRASE</a> <a>SIMPLIFIED</a> <a>INDUSTRIAL</a> <a>LENTICULAR</a> <a>ANNOTATION</a> <a>IDEOGRAPHS</a> <a>DIFFICULTY</a> <a>Ideograph</a> <a></a> <a>VISIGOTHIC</a> <a>EPIGRAPHIC</a> <a>SUPERFIXED</a> <a>DEVANAGARI</a> <a>REPETITION</a> <a>Surrogate</a> <a></a> <a>CONJOINING</a> <a>CENTERLINE</a> <a>COMMERCIAL</a> <a>ACROPHONIC</a> <a>HERMIONIAN</a> <a>EPIDAUREAN</a> <a>TROEZENIAN</a> <a>SESTERTIUS</a> <a>NIKOLSBURG</a> <a>ENT-SHAPED</a> <a>HIEROGLYPH</a> <a>NGGUAESHAE</a> <a>CONTINUING</a> <a>APOSTROFOS</a> <a>APOSTROFOI</a> <a>PROTOVARYS</a> <a>GORTHMIKON</a> <a>ENARMONIOS</a> <a>TRIGRAMMOS</a> <a>THREE-LINE</a> <a>SIX-STRING</a> <a>ARPEGGIATO</a> <a>SEMIBREVIS</a> <a>SEMIMINIMA</a> <a>IMPERFECTA</a> <a>PROLATIONE</a> <a>RECITATIVE</a> <a>HAND-HINGE</a> <a>HAND-ANGLE</a> <a>FLOORPLANE</a> <a>GLAGOLITIC</a> <a>TWENTY-TWO</a> <a>FLUTTERING</a> <a>DECORATIVE</a> <a>BRIGHTNESS</a> <a>MAGNIFYING</a> <a>COMPRESSED</a> <a>EXCLAMATION</a> <a>PALATALIZED</a> <a>NON-SPACING</a> <a>PUNCTUATION</a> <a>SALLALLAHOU</a> <a>RAHMATULLAH</a> <a>SUPERSCRIPT</a> <a>RECTANGULAR</a> <a>SUPRALINEAR</a> <a>HBASA-ESASA</a> <a>AFFRICATION</a> <a>DENOMINATOR</a> <a>CANDRABINDU</a> <a>MAHAAPRAANA</a> <a>INDEPENDENT</a> <a>PARENTHESES</a> <a>LONG-LEGGED</a> <a>VAMAGOMUKHA</a> <a>DIAERESIZED</a> <a>PERISPOMENI</a> <a>DIRECTIONAL</a> <a>004C;;;;N;T</a> <a>TRANSFINITE</a> <a>OPEN-HEADED</a> <a>PARENTHESIS</a> <a>UP-POINTING</a> <a>SYNCHRONOUS</a> <a>PARTNERSHIP</a> <a>CLUB-SPOKED</a> <a>RECTILINEAR</a> <a>WIDE-HEADED</a> <a>TOP-LIGHTED</a> <a>DIMENSIONAL</a> <a>INTEGRATION</a> <a>OVERLAPPING</a> <a>CONSECUTIVE</a> <a>SINGLE-LINE</a> <a>DOUBLE-LINE</a> <a>BACKSLANTED</a> <a>EQUILATERAL</a> <a>SACRIFICIAL</a> <a>DESCRIPTION</a> <a>IDEOGRAPHIC</a> <a>ALTERNATION</a> <a>SEMI-VOICED</a> <a>MULTIOCULAR</a> <a>SINOLOGICAL</a> <a>PLACEHOLDER</a> <a>BLACKLETTER</a> <a>ALTERNATIVE</a> <a>DOACHASHMEE</a> <a>REPLACEMENT</a> <a>BHATTIPROLU</a> <a>ENUMERATION</a> <a>LOW-FALLING</a> <a>THIRD-STAGE</a> <a>PARAKALESMA</a> <a>PARAKLITIKI</a> <a>THEMATISMOS</a> <a>MONOGRAMMOS</a> <a>FOUR-STRING</a> <a>IMPERFECTUM</a> <a>HAND-CIRCLE</a> <a>ALTERNATING</a> <a>HALF-CIRCLE</a> <a>FITZPATRICK</a> <a>INTERROBANG</a> <a>INFORMATION</a> <a>IRON-COPPER</a> <a>FINGER-POST</a> <a>LEFT-SHADED</a> <a>BACK-TILTED</a> <a>TRANSMISSION</a> <a>CROSSED-TAIL</a> <a>ABBREVIATION</a> <a>ARABIC-INDIC</a> <a>POSTPOSITION</a> <a>HORIZONTALLY</a> <a>NASALIZATION</a> <a>VOCALIZATION</a> <a>CANCELLATION</a> <a>ASTROLOGICAL</a> <a>CANTILLATION</a> <a>SOUTH-SLAVEY</a> <a>THREE-LEGGED</a> <a>ATHARVAVEDIC</a> <a>MIDDLE-WELSH</a> <a>MATHEMATICAL</a> <a>GREATER-THAN</a> <a>ROUND-TIPPED</a> <a>WEDGE-TAILED</a> <a>SEMICIRCULAR</a> <a>SUPERIMPOSED</a> <a>INTERSECTION</a> <a>INTERSECTING</a> <a>LEFT-LIGHTED</a> <a>SUBSTITUTION</a> <a>C-SIMPLIFIED</a> <a>J-SIMPLIFIED</a> <a>HIEROGLYPHIC</a> <a>CONTINUATION</a> <a>THREE-CIRCLE</a> <a>DOUBLE-LINED</a> <a>QUINDICESIMA</a> <a>SIXTY-FOURTH</a> <a>SPRECHGESANG</a> <a>AUGMENTATION</a> <a>INSTRUMENTAL</a> <a>SIMULTANEOUS</a> <a>HEART-SHAPED</a> <a>PHILOSOPHERS</a> <a>RIGHT-SHADED</a> <a>FRONT-TILTED</a> <a>PRISHTHAMATRA</a>  <a>INTERSYLLABIC</a> <a>SHORT-TWIG-AR</a> <a>VOWEL-CARRIER</a> <a>CRYPTOGRAMMIC</a> <a>LEFT-POINTING</a> <a>ANTICLOCKWISE</a> <a>APPROXIMATELY</a> <a>0338;;;;Y;NOT</a> <a>DOWN-POINTING</a> <a>DROP-SHADOWED</a> <a>CONCAVE-SIDED</a> <a>RIGHT-LIGHTED</a> <a>MORPHOLOGICAL</a> <a>LABIALIZATION</a> <a>INTERPOLATION</a> <a>TRANSPOSITION</a> <a>EGYPTOLOGICAL</a> <a>REGULUS</a> <a>VINEGAR</a> <a>BISMUTH</a> <a>SCEPTER</a> <a>STARRED</a>  <a>COMPATIBILITY</a> 
  <a>HEXIFORM</a> <a>UBHAYATO</a> <a>DELETION</a> <a>NO-BREAK</a> 
</div>

<div id="explorerdiv">
  <input type="range" id="explorer" min="1" max="500" value="23" step="1" onchange="explore()" />
</div>

<script>
function setup(){
  document.querySelectorAll('.unc').forEach(function(elem) {
    elem.setAttribute("onclick","copy(this.id)")
    elem.setAttribute("title","Click to copy")
  })
  document.querySelectorAll('.unn').forEach(function(elem) {
    pl = document.querySelectorAll('.unn')
    elem.setAttribute("onclick","view.value=this.parentElement.children[0].innerText;hex.value=this.innerText;lookup()")
    try{
     console.log(document.querySelectorAll('.unn').length)
      if (pl.length <=1){
         setTimeout(function(){ ajaxWiki(elem.innerText,elem.parentElement.nextSibling.nextSibling.id) }, 10)
         setTimeout(function(){ ajaxWiki(elem.innerText.split(" ")[0],elem.parentElement.nextSibling.nextSibling.id) }, 20)
         setTimeout(function(){ ajaxWiki(elem.innerText.split(" ")[1],elem.parentElement.nextSibling.nextSibling.id) }, 20)
         setTimeout(function(){ ajaxWiki(elem.innerText.split(" ")[2],elem.parentElement.nextSibling.nextSibling.id) }, 20)
         setTimeout(function(){ ajaxWiki(elem.innerText.split(" ")[3],elem.parentElement.nextSibling.nextSibling.id) }, 20)
         setTimeout(function(){ ajaxWiki(elem.innerText.split(" ")[4],elem.parentElement.nextSibling.nextSibling.id) }, 20)
         setTimeout(function(){ ajaxWiki(elem.innerText.split(" ")[5],elem.parentElement.nextSibling.nextSibling.id) }, 20)
         setTimeout(function(){ ajaxWiki(elem.innerText.split(" ")[6],elem.parentElement.nextSibling.nextSibling.id) }, 20)
         setTimeout(function(){ ajaxWiki(elem.innerText.split(" ")[7],elem.parentElement.nextSibling.nextSibling.id) }, 20)
         
        //~ }
      }    
    }catch(nope){}
  })
} 
document.querySelectorAll('a').forEach(function(elem){
     elem.setAttribute("onclick","hex.value=this.innerText;lookup()")
     elem.setAttribute("onkeydown","this.click()")
     elem.setAttribute("tabindex","0")
 
})
function check(_ૐ, ߐ){
document.getElementById(ߐ).innerHTML=document.getElementById(ߐ).innerHTML.replace(_ૐ,"<mark>"+_ૐ+"</mark>")
window.setTimeout(Ώ,5000)
}
function Ώ(){
   try{
     document.getElementById(ߐ).innerHTML=document.getElementById(ߐ).innerHTML.replace("mark","wbr")
  }catch(nope){}
}
function pushLoc() {
  if (history.pushState) {
    var newurl = window.location.protocol + "//" + window.location.host + window.location.pathname + '#' + hex.value;
    window.history.pushState({path:newurl},'',newurl);
  }
}
window.onhashchange = function() { 
  hex.value = window.location.hash.split('#')[1]
  lookup()  
}

function* genPage(){
  var index = 0;
  while(index < 136690){
    yield index++ * 274
  }
}
var page = genPage(),stock = [],j = 0
do 
  stock.push(page.next().value),
  j++
while (j<500)
 
function explore(){
  console.log(stock[explorer.value])
  var h = stock[explorer.value]
  var i = h
  var j = i + 274 
  console.log(i+" "+j)
  hex.value = " 📟"+explorer.value
  main.innerHTML = ""
  main.innerHTML = "<div id='list'>"
  for (i;i<j;i++){
    u = document.createElement("span")
    u.innerHTML = "<a title='(Linux|Hex): [CTRL+SHIFT]+u"+(i).toString(16)+"\nHtml entity: &# "+i+";\n&#x"+(i).toString(16)+";\n(Win|Dec): [ALT]+"+i+"' onmouseover='this.focus()' onclick='hex.value=\""+(i).toString(16)+"\";lookup()' style='cursor:pointer' target='new'>"+"&#"+i+";</a>"
    main.appendChild(u)
  }
  pushLoc()
}

function getSelectionText() {
    var text = "";
    if (window.getSelection) {
        text = window.getSelection().toString();
    } else if (document.selection && document.selection.type != "Control") {
        text = document.selection.createRange().text;
    }
    return text;
}

main.ondblclick = (function(){ 
   hex.value = getSelectionText()
   lookup()
})
main.oncontextmenu = (function(){ 
   hex.value = getSelectionText()
   lookup()
})

window.onload= function() { 
  hex.value = window.location.hash.split('#')[1]
  if (hex.value == "undefined"){ hex.value = "oncoming"}
  if (window.location.hash.substring(1, 16) == "%20%F0%9F%93%9F"){explorer.value = hex.value.slice(15);explore() }
  lookup()  
}

function synonyms(me){
  var url = 'https://api.datamuse.com/words?ml=' + hex.value;
  fetch(url).then(v => v.json()).then((function(v){ 
    syn = JSON.stringify(v)
    console.log(syn)
    syn = JSON.parse(syn)
    for(var k in syn){
      main.innerHTML += "<span class='unp'>"+syn[k].word+"</span> "
      }
    })
  ) 
  ajaxWiki(hex.value,"main")
  check(hex.value,"main")
}
    </script>
  </body>
</html>
