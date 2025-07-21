import React, { Suspense, lazy } from 'react'
import { BrowserRouter as Router, Routes, Route, useLocation } from 'react-router-dom'
import Navbar from './components/Navbar'
import Footer from './components/Footer'
import LoadingSpinner from './components/LoadingSpinner'
import MenuBar from './components/MenuBar'

// Lazy load route components
const Home = lazy(() => import('./pages/Home'))
const Shop = lazy(() => import('./pages/Shop'))
const Cart = lazy(() => import('./pages/Cart'))
const Checkout = lazy(() => import('./pages/Checkout'))
const Contact = lazy(() => import('./pages/Contact'))
const ProductDetail = lazy(() => import('./pages/ProductDetail'))

function AppContent() {
  const location = useLocation();
  const isHome = location.pathname === '/';
  return (
    <div className="wrapper">
      <Navbar />
      {!isHome && <MenuBar />}
      <Suspense fallback={<LoadingSpinner />}>
        <Routes>
          <Route path="/" element={<Home />} />
          <Route path="/shop" element={<Shop />} />
          <Route path="/cart" element={<Cart />} />
          <Route path="/checkout" element={<Checkout />} />
          <Route path="/contact" element={<Contact />} />
          <Route path="/product/:id" element={<ProductDetail />} />
        </Routes>
      </Suspense>
      <Footer />
    </div>
  );
}

function App() {
  return (
    <Router>
      <AppContent />
    </Router>
  )
}

export default App
