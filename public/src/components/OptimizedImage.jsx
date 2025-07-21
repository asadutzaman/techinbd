import React from 'react';
import useImageLoader from '../hooks/useImageLoader';

const OptimizedImage = ({ src, alt, className, style }) => {
  const { imageSrc, isLoading } = useImageLoader(src);

  return (
    <div className={`image-container ${isLoading ? 'loading' : ''}`} style={{ overflow: 'hidden', ...style }}>
      <img
        src={imageSrc}
        alt={alt}
        className={`optimized-image ${className || ''}`}
        style={{
          transition: 'filter 0.3s ease-in-out',
          filter: isLoading ? 'blur(10px)' : 'none',
          width: '100%',
          height: '100%',
          objectFit: 'cover'
        }}
        loading="lazy"
      />
    </div>
  );
};

export default OptimizedImage;