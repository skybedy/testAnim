    new Vue({
      el: '#content',
      
      data:{
        blokNabidka: true,
        blokProdukt: false,
        blokKosik: false,
        blokSeznamObjednavek: false,
        blokDetailObjednavky: false,
        blokXmlUpload: false,
        nabidkaData: "",
        produktId : "",
        produktPopis: "",
        pocetKusu: "",
        vypisKosiku: "",
        seznamVsechObjednavek:"",
        detailJedneObjednavky:"",
        posts : {
          jmeno:"",
          prijmeni:"",
          ulice:"",
          mesto:"",
          psc:"",
          email:""
        },

      },
      
      mounted:function(){
        this.nabidka() //method1 will execute at pageload
        },
      
      
      methods: {
        nabidka:function(){
            try{
                (async () => {
                    const response = await  axios.get('/nabidka');
                    this.nabidkaData = response.data;
                    this.blokProdukt = false;
                    this.blokKosik = false;
                    this.blokDetailObjednavky = false;
                    this.blokSeznamObjednavek = false;
                    this.blokXmlUpload = false;
                    this.blokNabidka = true;
                    })();
      
              }catch(err){
                console.log(err)
              }
      
        },
        produkt(){
          try{
            (async () => {
                const response = await  axios.get('/produkt/'+this.produktId);
                this.blokProdukt = true;
                this.blokKosik = false;
                this.blokDetailObjednavky = false;
                this.blokSeznamObjednavek = false;
                this.blokXmlUpload = false;
                this.blokNabidka = false;

                this.produktPopis = response.data;

            })();
  
          }catch(err){
            console.log(err)
          }

        },

        pridaniDoKosiku(){
          try{
            (async () => {
                const response = await  axios.get('/pridaniDoKosiku/'+this.produktId+"/"+this.pocetKusu);
                alert("Polo??ka p??id??na do ko????ku");
                this.blokNabidka = true;
                this.blokProdukt = false;
                this.blokDetailObjednavky = false;

                this.pocetKusu = "";
            })();
  
          }catch(err){
            console.log(err)
          }

        },

        kosik(){
          try{
            (async () => {
                const response = await  axios.get('/kosik');
                if(response.data != null){
                    this.vypisKosiku = response.data;
                    this.blokProdukt = false;
                    this.blokKosik = true;
                    this.blokDetailObjednavky = false;
                    this.blokSeznamObjednavek = false;
                    this.blokXmlUpload = false;
                    this.blokNabidka = false;
    
                }else{
                  alert("V ko????ku nic nen??");
                }

            })();
  
          }catch(err){
            console.log(err)
          }

        },
        objednavka(){
          try{
            (async () => {
                const response = await  axios.post('/objednavka',this.posts,{headers: {'Content-Type': 'application/x-www-form-urlencoded'}});
                alert("Objedn??vka byla odesl??na");
                this.blokNabidka = true;
                this.blokKosik = false;
                this.blokDetailObjednavky = false;




            })();
  
          }catch(err){
            console.log(err)
          }  
        },
        seznamObjednavek(){
          try{
            (async () => {
                const response = await  axios.get('/seznamObjednavek');
                if(response.data != null){
                  this.seznamVsechObjednavek = response.data;
                  this.blokNabidka = false;
                  this.blokProdukt = false;
                  this.blokKosik = false;
                  this.blokDetailObjednavky = false;
                  this.blokSeznamObjednavek = true;
              }else{
                alert("????dn?? objedn??vky nem??me");
              }
            })();
  
          }catch(err){
            console.log(err)
          }  
        },
        detailObjednavky(e){
          try{
            (async () => {
                idObjednavky = e.target.innerText;
                const response = await  axios.get('/detailObjednavky/'+idObjednavky);
                this.detailJedneObjednavky = response.data;
                this.blokSeznamObjednavek = false;
                this.blokDetailObjednavky = true;


            })();
  
          }catch(err){
            console.log(err)
          }  
        },
     
        truncateDb(){
          try{
            (async () => {
               alert("Nen?? tady preloader, po??kej pak na hl????ku 'Vypr??zdn??no' a pak ud??lej reload");
                const response = await  axios.get('/truncateDb');
                alert('Vypr??zdn??no');
                this.blokProdukt = false;
                this.blokKosik = false;
                this.blokDetailObjednavky = false;
                this.blokSeznamObjednavek = false;
                this.blokXmlUpload = false;
                this.blokNabidka = true;
            })();
  
          }catch(err){
            console.log(err)
          }
     
        },
        xmlUploadForm(){
          this.blokNabidka = false;
          this.blokProdukt = false;
          this.blokKosik = false;
          this.blokDetailObjednavky = false;
          this.blokSeznamObjednavek = false;
          this.blokXmlUpload = true;
        },
        xmlUpload() {
          var formData = new FormData();
          var xmlFile = document.querySelector('#xmlFile');
          
          formData.append("xmlFile", xmlFile.files[0]);
          axios.post('/xmlUpload', formData, {
              headers: {
                'Content-Type': 'multipart/form-data'
              }
          }).then(function () {
            alert('Nahr??no');
          })  
        },
        dbExport(){
          try{
            (async () => {
                const response = await  axios.get('/dbExport');


            })();
  
          }catch(err){
            console.log(err)
          } 
        }

      } //konec methods
    })
  