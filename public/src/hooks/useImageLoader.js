import { useState, useEffect } from 'react';

const useImageLoader = (src, placeholder = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=') => {
  const [imageSrc, setImageSrc] = useState(placeholder);
  const [imageRef, setImageRef] = useState();

  const onLoad = (event) => {
    event.target.classList.add('loaded');
    setImageSrc(src);
  };

  const onError = (event) => {
    event.target.classList.add('has-error');
    setImageSrc(placeholder);
  };

  useEffect(() => {
    const img = new Image();
    img.src = src;
    img.addEventListener('load', onLoad);
    img.addEventListener('error', onError);
    setImageRef(img);

    return () => {
      img.removeEventListener('load', onLoad);
      img.removeEventListener('error', onError);
    };
  }, [src]);

  return {
    imageSrc,
    isLoading: imageSrc === placeholder,
    imageRef
  };
};

export default useImageLoader;