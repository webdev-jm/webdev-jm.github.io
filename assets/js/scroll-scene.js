import * as THREE from 'three';
import { OBJLoader } from 'three/addons/loaders/OBJLoader.js';

const canvas = document.getElementById('bg-canvas');
const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

if (canvas && !prefersReducedMotion) {
    const scene = new THREE.Scene();
    scene.background = new THREE.Color(0x05070d);

    const camera = new THREE.PerspectiveCamera(60, window.innerWidth / window.innerHeight, 0.1, 100);
    camera.position.z = 8;

    const renderer = new THREE.WebGLRenderer({ canvas, antialias: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

    const particleCount = window.innerWidth < 768 ? 350 : 900;
    const positions = new Float32Array(particleCount * 3);
    const particleOriginDistances = new Float32Array(particleCount);
    for (let i = 0; i < particleCount; i++) {
        const x = (Math.random() - 0.5) * 22;
        const y = (Math.random() - 0.5) * 22;
        const z = (Math.random() - 0.5) * 22;
        positions[i * 3] = x;
        positions[i * 3 + 1] = y;
        positions[i * 3 + 2] = z;
        particleOriginDistances[i] = Math.sqrt(x * x + y * y + z * z);
    }
    const particleColors = new Float32Array(particleCount * 3);
    function createDiscTexture() {
        const size = 64;
        const discCanvas = document.createElement('canvas');
        discCanvas.width = size;
        discCanvas.height = size;
        const ctx = discCanvas.getContext('2d');
        const gradient = ctx.createRadialGradient(size / 2, size / 2, 0, size / 2, size / 2, size / 2);
        gradient.addColorStop(0, 'rgba(255, 255, 255, 1)');
        gradient.addColorStop(0.4, 'rgba(255, 255, 255, 0.6)');
        gradient.addColorStop(1, 'rgba(255, 255, 255, 0)');
        ctx.fillStyle = gradient;
        ctx.fillRect(0, 0, size, size);
        return new THREE.CanvasTexture(discCanvas);
    }

    const particlesGeometry = new THREE.BufferGeometry();
    particlesGeometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    particlesGeometry.setAttribute('color', new THREE.BufferAttribute(particleColors, 3).setUsage(THREE.DynamicDrawUsage));
    const particlesMaterial = new THREE.PointsMaterial({
        size: 0.045,
        color: 0x818cf8,
        map: createDiscTexture(),
        vertexColors: true,
        alphaTest: 0.01,
        depthWrite: false,
        transparent: true,
        opacity: 0.7,
    });
    const particles = new THREE.Points(particlesGeometry, particlesMaterial);
    scene.add(particles);

    const hemiLight = new THREE.HemisphereLight(0x4f5bd5, 0x0a0a12, 0.8);
    scene.add(hemiLight);

    const keyLight = new THREE.DirectionalLight(0x818cf8, 2.2);
    keyLight.position.set(4, 5, 6);
    scene.add(keyLight);

    const fillLight = new THREE.DirectionalLight(0xf472b6, 0.6);
    fillLight.position.set(-5, -2, 4);
    scene.add(fillLight);

    const textureLoader = new THREE.TextureLoader();
    const modelBase = 'assets/models/moon/';
    const colorMap = textureLoader.load(modelBase + 'textures/moon-diffuse.png');
    colorMap.colorSpace = THREE.SRGBColorSpace;
    const bumpMap = textureLoader.load(modelBase + 'textures/moon-bump.png');

    function buildMoonMaterial() {
        return new THREE.MeshStandardMaterial({
            map: colorMap,
            bumpMap,
            bumpScale: 0.05,
            metalness: 0,
            roughness: 0.95,
            transparent: true,
            opacity: 0,
        });
    }

    const fadeMaterials = [];

    const moonGroup = new THREE.Group();
    scene.add(moonGroup);

    let moonModel = null;

    new OBJLoader().load(
        modelBase + 'moon.obj',
        (object) => {
            object.traverse((child) => {
                if (!child.isMesh) {
                    return;
                }
                child.material = buildMoonMaterial();
                fadeMaterials.push(child.material);
            });

            moonGroup.add(object);
            moonGroup.scale.setScalar(1);
            moonModel = object;
        },
        undefined,
        (error) => console.error('Failed to load moon model', error)
    );

    let mouseX = 0;
    let mouseY = 0;
    window.addEventListener('mousemove', (event) => {
        mouseX = (event.clientX / window.innerWidth) * 2 - 1;
        mouseY = (event.clientY / window.innerHeight) * 2 - 1;
    });

    function resize() {
        camera.aspect = window.innerWidth / window.innerHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(window.innerWidth, window.innerHeight, false);
    }
    window.addEventListener('resize', resize);
    resize();

    let scrollProgress = 0;
    function updateScrollProgress() {
        const max = document.documentElement.scrollHeight - window.innerHeight;
        scrollProgress = max > 0 ? Math.min(Math.max(window.scrollY / max, 0), 1) : 0;
    }
    window.addEventListener('scroll', updateScrollProgress, { passive: true });
    updateScrollProgress();

    const clock = new THREE.Clock();
    const tmpColor = new THREE.Color();
    const localCameraPosition = new THREE.Vector3();
    const inverseParticlesQuaternion = new THREE.Quaternion();
    const moonExclusionRadius = 2.4;
    const cameraExclusionRadius = 2.5;
    const farBrightnessRadius = 15;
    const minBrightness = 0.3;
    const maxBrightness = 1.4;

    function animate() {
        const elapsed = clock.getElapsedTime();

        moonGroup.rotation.y = elapsed * 0.18 + scrollProgress * Math.PI * 2;
        moonGroup.rotation.x = Math.sin(elapsed * 0.15) * 0.05 + scrollProgress * 0.4;

        if (moonModel) {
            const breath = 1 + Math.sin(elapsed * 0.6) * 0.035;
            moonModel.scale.setScalar(breath);
        }

        fadeMaterials.forEach((material) => {
            material.opacity += (1 - material.opacity) * 0.04;
        });

        particles.rotation.y = elapsed * 0.02 + scrollProgress * 1.5;
        particles.rotation.x = scrollProgress * 0.6;

        const hue = (elapsed * 0.015 + scrollProgress * 0.4) % 1;
        tmpColor.setHSL(hue, 0.65, 0.65);
        keyLight.color.lerp(tmpColor, 0.04);
        particlesMaterial.color.lerp(tmpColor, 0.04);

        camera.position.x += (mouseX * 1.2 - camera.position.x) * 0.03;
        camera.position.y += (-mouseY * 1.2 - camera.position.y - scrollProgress * 1.5) * 0.03;
        camera.position.z = 8 - scrollProgress * 1.5;
        camera.lookAt(scene.position);

        inverseParticlesQuaternion.copy(particles.quaternion).invert();
        localCameraPosition.copy(camera.position).applyQuaternion(inverseParticlesQuaternion);

        const colorArray = particlesGeometry.attributes.color.array;
        for (let i = 0; i < particleCount; i++) {
            const ix = i * 3;
            const dx = positions[ix] - localCameraPosition.x;
            const dy = positions[ix + 1] - localCameraPosition.y;
            const dz = positions[ix + 2] - localCameraPosition.z;
            const distanceToCamera = Math.sqrt(dx * dx + dy * dy + dz * dz);

            let brightness = 0;
            if (particleOriginDistances[i] >= moonExclusionRadius && distanceToCamera >= cameraExclusionRadius) {
                const t = THREE.MathUtils.clamp(distanceToCamera / farBrightnessRadius, 0, 1);
                brightness = THREE.MathUtils.lerp(minBrightness, maxBrightness, t);
            }

            colorArray[ix] = brightness;
            colorArray[ix + 1] = brightness;
            colorArray[ix + 2] = brightness;
        }
        particlesGeometry.attributes.color.needsUpdate = true;

        renderer.render(scene, camera);
        requestAnimationFrame(animate);
    }

    animate();
}
