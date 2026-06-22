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

    const TEXTURED_MATERIAL_NAMES = new Set([
        'Sphere_Bot',
        'Sphere_Bot_Leg_Nor',
        'Sphere_Bot_Sphere_Bot_color_2.png',
    ]);

    const textureLoader = new THREE.TextureLoader();
    const modelBase = 'assets/models/sphere-bot/';
    const colorMap = textureLoader.load(modelBase + 'textures/Sphere_Bot_color_2.jpg');
    colorMap.colorSpace = THREE.SRGBColorSpace;
    const normalMap = textureLoader.load(modelBase + 'textures/Sphere_Bot_nmap.jpg');
    const metalnessMap = textureLoader.load(modelBase + 'textures/Sphere_Bot_metalness.jpg');
    const roughnessMap = textureLoader.load(modelBase + 'textures/Sphere_Bot_rough.jpg');
    const aoMap = textureLoader.load(modelBase + 'textures/Sphere_Bot_ao.jpg');

    function buildBotMaterial() {
        return new THREE.MeshStandardMaterial({
            map: colorMap,
            normalMap,
            metalnessMap,
            roughnessMap,
            aoMap,
            metalness: 1,
            roughness: 1,
            transparent: true,
            opacity: 0,
        });
    }

    function buildAccentMaterial(color) {
        return new THREE.MeshStandardMaterial({
            color,
            metalness: 0.4,
            roughness: 0.45,
            transparent: true,
            opacity: 0,
        });
    }

    const fadeMaterials = [];

    function resolveMaterial(originalName) {
        if (originalName === 'Material.002') {
            return buildAccentMaterial(0xcc1f1f);
        }
        if (originalName === 'Material') {
            return buildAccentMaterial(0x14151a);
        }
        return buildBotMaterial();
    }

    const botGroup = new THREE.Group();
    scene.add(botGroup);

    new OBJLoader().load(
        modelBase + 'sphere-bot.obj',
        (object) => {
            object.traverse((child) => {
                if (!child.isMesh) {
                    return;
                }
                if (child.geometry.attributes.uv && !child.geometry.attributes.uv2) {
                    child.geometry.setAttribute('uv2', child.geometry.attributes.uv);
                }
                if (Array.isArray(child.material)) {
                    child.material = child.material.map((mat) => resolveMaterial(mat.name));
                } else {
                    child.material = resolveMaterial(child.material.name);
                }
                fadeMaterials.push(child.material);
            });

            object.position.set(0, -1, 0);
            botGroup.add(object);
            botGroup.scale.setScalar(1.6);
        },
        undefined,
        (error) => console.error('Failed to load sphere-bot model', error)
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

        botGroup.rotation.y = elapsed * 0.18 + scrollProgress * Math.PI * 2;
        botGroup.rotation.x = Math.sin(elapsed * 0.15) * 0.05 + scrollProgress * 0.4;

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
