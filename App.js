let name = "Driver Nav";
const version= 1.0;

//waiting for page to load
document.addEventListener('DOMContentLoaded', ()=>{
    initializeApp();
});

function initializeApp(){
//getting the camera button elements for html file
const StartCAM= document.getElementById('startCamera');
const StopCAM= document.getElementById('stopCamera');

//adding event listeners so that it looks out for the click
StartCAM.addEventListener('click', startCamera);
StopCAM.addEventListener('click', stopCamera);
};

let cameraStream= null;

async function startCamera() { // this stops the browser from freezing while waiting for hardware permissions (the camera). 
    try{
        //asks for camera access
        const backcamera= {
            video: {
                facingMode: 'environment', // the back camera on your phone
                width: { ideal: 1280 },
                height: { ideal: 720 }
            }
        };
        //getting camera stream
        cameraStream= await navigator.mediaDevices.getUserMedia(backcamera);
        //shows in the videoelement 
        const video=document.getElementById('videoElement');
        video.srcObject = cameraStream;

        updateStatus('Camera Started!')

    }   //If camera permisons were not given then this happens
    catch(error){
        console.error('Camera Error:',error);
        updateStatus('Camera Access Denied');
        alert('Please allow camera permisions in you settings :)')
    }
}

function stopCamera(){
    if (cameraStream){

        cameraStream.getTracks().forEach(Track => Track.stop());
        cameraStream= null

        //to clear the video
        const video= document.getElementById('videoElement');
        video.srcObject = null;
        updateStatus('Camera Stopped!');
    }
}

function updateStatus(message) {
    document.getElementById('statusText').textContent = message;
}