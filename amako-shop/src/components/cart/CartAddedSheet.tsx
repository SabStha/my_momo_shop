import React, {useEffect, useRef, useState} from 'react';
import {Modal, View, Text, Image, Pressable, Animated, Easing, BackHandler, Platform, AccessibilityInfo} from 'react-native';

type Payload = {
  name: string;
  price: number;
  qty: number;
  thumb?: string;
  cartCount: number;
  cartTotal: number;
};

type Props = {
  visible: boolean;
  payload?: Payload | null;
  onClose: () => void;
  onViewCart: () => void;
  onCheckout: () => void;
};

const SHEET_HEIGHT = 420;

export default function CartAddedSheet({visible, payload, onClose, onViewCart, onCheckout}: Props) {
  const translateY = useRef(new Animated.Value(SHEET_HEIGHT)).current;
  const [timer, setTimer] = useState<ReturnType<typeof setTimeout> | null>(null);

  useEffect(() => {
    if (!visible) return;
    Animated.timing(translateY, {
      toValue: 0,
      duration: 260,
      easing: Easing.out(Easing.cubic),
      useNativeDriver: true,
    }).start();

    const bh = BackHandler.addEventListener('hardwareBackPress', () => {
      onClose();
      return true;
    });

    // auto close after 18s (tripled from 6s)
    const t = setTimeout(onClose, 18000);
    setTimer(t);

    // announce for screen readers
    AccessibilityInfo.announceForAccessibility?.('Added to Cart');

    return () => {
      bh.remove();
      if (t) clearTimeout(t);
    };
  }, [visible]);

  const pauseTimer = () => { if (timer) clearTimeout(timer); };
  const resumeTimer = () => { const t = setTimeout(onClose, 9000); setTimer(t); };

  if (!payload) return null;

  return (
    <Modal 
      visible={visible} 
      transparent 
      animationType="none" 
      onRequestClose={onClose} 
      statusBarTranslucent
      presentationStyle="overFullScreen"
    >
      <Pressable
        onPress={onClose}
        style={{flex:1, backgroundColor:'rgba(0,0,0,0.35)', zIndex: 9998}}
        accessibilityLabel="Close added to cart sheet"
        accessible
      />
      <Animated.View
        style={{
          position:'absolute',
          left:0, right:0, bottom:0,
          backgroundColor:'#fff',
          borderTopLeftRadius:16, borderTopRightRadius:16,
          padding:16,
          transform:[{translateY}],
          elevation:24,
          shadowColor:'#000', shadowOpacity:0.2, shadowRadius:10, shadowOffset:{width:0,height:-2},
          zIndex: 9999
        }}
        onTouchStart={pauseTimer}
        onTouchEnd={resumeTimer}
      >
        {/* Header */}
        <View style={{flexDirection:'row', alignItems:'center', marginBottom:12}}>
          <View style={{width:32, height:32, borderRadius:16, backgroundColor:'#27ae60', alignItems:'center', justifyContent:'center', marginRight:12}}>
            <Text style={{color:'#fff', fontWeight:'700'}}>✓</Text>
          </View>
          <View>
            <Text style={{fontSize:18, fontWeight:'700'}}>Added to Cart!</Text>
            <Text style={{color:'#6c757d'}}>Item successfully added</Text>
          </View>
          <Pressable
            onPress={onClose}
            style={{marginLeft:'auto', padding:4}}
            accessibilityLabel="Close"
          >
            <Text style={{fontSize:22}}>×</Text>
          </Pressable>
        </View>

        {/* Item row */}
        <View style={{flexDirection:'row', alignItems:'center', padding:12, backgroundColor:'#f8fff8', borderRadius:12, borderWidth:1, borderColor:'#e9ecef', marginBottom:12}}>
          <Image source={{uri: payload.thumb || 'https://via.placeholder.com/96'}} style={{width:56, height:56, borderRadius:8, marginRight:10}} />
          <View style={{flex:1}}>
            <Text style={{fontWeight:'600'}} numberOfLines={1}>{payload.name}</Text>
            <Text style={{color:'#6c757d'}}>Quantity: {payload.qty}</Text>
          </View>
          <Text style={{fontWeight:'700'}}>Rs.{(Number(payload.price) || 0).toFixed(2)}</Text>
        </View>

        {/* Total row */}
        <View style={{flexDirection:'row', alignItems:'center', justifyContent:'space-between', padding:12, borderRadius:12, borderWidth:1, borderColor:'#e9ecef', marginBottom:14}}>
          <View>
            <Text style={{fontWeight:'600'}}>Cart Total</Text>
            <Text style={{color:'#6c757d'}}>Free delivery</Text>
          </View>
          <Text style={{fontWeight:'800'}}>Rs.{(Number(payload.cartTotal) || 0).toFixed(2)}</Text>
        </View>

        {/* Actions */}
        <View style={{gap:10}}>
          <Pressable 
            onPress={() => {
              console.log('🛒 CartAddedSheet: View Cart button pressed');
              onViewCart();
            }} 
            style={{backgroundColor:'#8b0000', paddingVertical:14, borderRadius:12, alignItems:'center'}}
          >
            <Text style={{color:'#fff', fontWeight:'700'}}>View Cart ({payload.cartCount || 0})</Text>
          </Pressable>
          <Pressable 
            onPress={() => {
              console.log('🛒 CartAddedSheet: Checkout button pressed');
              onCheckout();
            }} 
            style={{backgroundColor:'#2ecc71', paddingVertical:14, borderRadius:12, alignItems:'center'}}
          >
            <Text style={{color:'#fff', fontWeight:'700'}}>Checkout Now</Text>
          </Pressable>
          <Pressable 
            onPress={() => {
              console.log('🛒 CartAddedSheet: Continue Shopping button pressed');
              onClose();
            }} 
            style={{backgroundColor:'#f1f3f5', paddingVertical:14, borderRadius:12, alignItems:'center'}}
          >
            <Text style={{fontWeight:'700'}}>Continue Shopping</Text>
          </Pressable>
        </View>

        {/* Optional recommendations slot */}
        {/* Place your "You might also like" horizontally scrollable list here */}
      </Animated.View>
    </Modal>
  );
}
