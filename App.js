const name = "Driver Nav";
const version= "Version 2.0";
console. log(name);
console.log(version);

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
        const video = document.getElementById('videoElement');
        video.srcObject = cameraStream;

        updateStatus('Camera Started!');

        // Load AI model if not already loaded
        if (!Model) {
            updateStatus('Loading AI model...');
            const loaded = await loadAiModel();
            if (!loaded) {
                updateStatus('Failed to load AI model');
                return;
            }
        }

        // When video has dimensions, size overlay and start detection
        const onVideoReady = () => {
            drawHUD(); // sizes canvas to video
            startDetection();
            updateStatus('Camera Started! Tracking...');
        };
        if (video.videoWidth > 0) {
            onVideoReady();
        } else {
            video.onloadedmetadata = onVideoReady;
        }

    }   //If camera permisons were not given then this happens
    catch(error){
        console.error('Camera Error:',error);
        updateStatus('Camera Access Denied');
        alert('Please allow camera permissions in your settings :)')
    }
}

function stopCamera(){
    if (cameraStream){
        stopDetection();
        cameraStream.getTracks().forEach(Track => Track.stop());
        cameraStream = null;

        //to clear the video
        const video = document.getElementById('videoElement');
        video.srcObject = null;
        updateStatus('Camera Stopped!');
    }
}

function updateStatus(message) {
    document.getElementById('statusText').textContent = message;
}

// Canvas lets you draw graphics on top of video

function drawHUD(){
 const canvas = document.getElementById('overLayCanvas');
 const video = document.getElementById('videoElement');

 if (!video || !video.videoWidth) return;


//Match the canvas size to the video
canvas.width = video.videoWidth;
canvas.height = video.videoHeight;

const Ctx = canvas.getContext('2d')

//draw center guidlines 
Ctx.strokeStyle = '#4CAF50';
Ctx.lineWidth = 3;
Ctx.setLineDash([20 , 10]);
Ctx.beginPath();
Ctx.moveTo(canvas.width / 2, 0);
Ctx.lineTo(canvas.width / 2, canvas.height);
Ctx.stroke();
}

//loading Ai model
let Model = null;

async function loadAiModel() {
    try {
        //loading pre train object detection model
        Model = await cocoSsd.load();
        console.log('Ai Model Loaded!');
        return true;
    }  catch (error){
        console.error('error loading model :,(',error)
        return false;
    }
    
}

async function detectObjects(video) {
    if (!Model || !video.videoWidth) return;

    // Keep overlay canvas sized to video (in case of resize)
    const canvas = document.getElementById('overLayCanvas');
    if (canvas.width !== video.videoWidth || canvas.height !== video.videoHeight) {
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
    }

    try{
        //run detection on video frame 
        const Predictions = await Model.detect(video);
 // predictions = [
        //   { class: 'car', score: 0.95, bbox: [x, y, width, height] },
        //   { class: 'person', score: 0.87, bbox: [x, y, width, height] }
        // ]

        //draw boxes on canvas
        drawDetections(Predictions);
        //dysplay in UI
        displayDetection(Predictions);
    } 
    catch(error){
        console.log('Detection Error:',error);
    }
    
}
function drawDetections(Predictions){
    const canvas = document.getElementById('overLayCanvas');
    const Ctx = canvas.getContext('2d');

    //clears the previous drawing 
    Ctx.clearRect(0, 0, canvas.width, canvas.height);

    // Redraw center guidelines (HUD)
    Ctx.strokeStyle = '#4CAF50';
    Ctx.lineWidth = 3;
    Ctx.setLineDash([20, 10]);
    Ctx.beginPath();
    Ctx.moveTo(canvas.width / 2, 0);
    Ctx.lineTo(canvas.width / 2, canvas.height);
    Ctx.stroke();

    Predictions.forEach(detection =>{
        const [x, y, width, height]= detection.bbox

        // Draw bounding box
        Ctx.strokeStyle = '#FF9800';
        Ctx.lineWidth = 2;
        Ctx.strokeRect(x, y, width, height);
        
        // Draw label
        Ctx.fillStyle = 'rgba(0, 0, 0, 0.7)';
        Ctx.fillRect(x, y - 20, width, 20);
        Ctx.fillStyle = '#ffffff';
        Ctx.font = '12px Arial';
        Ctx.fillText(
            `${detection.class} ${Math.round(detection.score * 100)}%`,
            x + 5,
            y - 5
        );
    })
}

function displayDetection(predictions) {
    if (!predictions || predictions.length === 0) {
        return;
    }
    const top = predictions
        .sort((a, b) => b.score - a.score)
        .slice(0, 3)
        .map(p => `${p.class} ${Math.round(p.score * 100)}%`)
        .join(', ');
    if (top) {
        document.getElementById('statusText').textContent = `Detected: ${top}`;
    }
}

//Now for continous detection...YAY :).... im so tired send help
let detectionInterval = null;

function startDetection(){
    const video = document.getElementById('videoElement')

    //i get the detection to every 500ms seems to be a good speed
    detectionInterval= setInterval(() =>{
        detectObjects(video)
    }, 500)//the speed
}

function stopDetection(){
    if (detectionInterval) {
        clearInterval(detectionInterval);
        detectionInterval = null;
    }
}