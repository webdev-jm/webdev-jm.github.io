import * as THREE from 'three';

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

    const shapes = [
        new THREE.IcosahedronGeometry(2.4, 1),
        new THREE.TorusKnotGeometry(1.5, 0.45, 120, 16),
        new THREE.OctahedronGeometry(2.6, 0),
    ].map(
        (geometry) =>
            new THREE.Mesh(
                geometry,
                new THREE.MeshBasicMaterial({ wireframe: true, transparent: true, opacity: 0 })
            )
    );
    shapes.forEach((shape) => scene.add(shape));

    let activeShapeIndex = 0;

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
        activeShapeIndex = Math.min(Math.floor(scrollProgress * shapes.length), shapes.length - 1);
    }
    window.addEventListener('scroll', updateScrollProgress, { passive: true });
    updateScrollProgress();

    const clock = new THREE.Clock();
    const tmpColor = new THREE.Color();

    function animate() {
        const elapsed = clock.getElapsedTime();

        shapes.forEach((shape, i) => {
            shape.rotation.x = elapsed * (0.1 + i * 0.04) + scrollProgress * Math.PI * 2;
            shape.rotation.y = elapsed * (0.15 + i * 0.03) + scrollProgress * Math.PI;
            const targetOpacity = i === activeShapeIndex ? 0.5 : 0;
            shape.material.opacity += (targetOpacity - shape.material.opacity) * 0.05;
        });

        particles.rotation.y = elapsed * 0.02 + scrollProgress * 1.5;
        particles.rotation.x = scrollProgress * 0.6;

        const hue = (elapsed * 0.01 + scrollProgress * 0.4) % 1;
        tmpColor.setHSL(hue, 0.65, 0.7);
        particlesMaterial.color.lerp(tmpColor, 0.05);
        shapes[activeShapeIndex].material.color.lerp(tmpColor, 0.05);

        camera.position.x += (mouseX * 1.2 - camera.position.x) * 0.03;
        camera.position.y += (-mouseY * 1.2 - camera.position.y - scrollProgress * 1.5) * 0.03;
        camera.position.z = 8 - scrollProgress * 1.5;
        camera.lookAt(scene.position);

        renderer.render(scene, camera);
        requestAnimationFrame(animate);
    }

    animate();
}
