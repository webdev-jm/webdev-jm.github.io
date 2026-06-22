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
    for (let i = 0; i < particleCount; i++) {
        positions[i * 3] = (Math.random() - 0.5) * 22;
        positions[i * 3 + 1] = (Math.random() - 0.5) * 22;
        positions[i * 3 + 2] = (Math.random() - 0.5) * 22;
    }
    const particlesGeometry = new THREE.BufferGeometry();
    particlesGeometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    const particlesMaterial = new THREE.PointsMaterial({
        size: 0.045,
        color: 0x818cf8,
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

        renderer.render(scene, camera);
        requestAnimationFrame(animate);
    }

    animate();
}
