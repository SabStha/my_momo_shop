import React from 'react';
import {Modal, View, Text, Image, Pressable, ScrollView, StyleSheet} from 'react-native';
import {MaterialCommunityIcons as MCI} from '@expo/vector-icons';

type Props = {
  visible: boolean;
  onClose: () => void;
  data: {
    image: string;
    ingredients: string;
    allergens: string;
    nutrition: {cal:string; size:string; prep:string; spice:string};
    dietary: string;
  };
};

export default function FoodInfoSheet({visible, onClose, data}: Props) {
  return (
    <Modal visible={visible} animationType="fade" transparent onRequestClose={onClose}>
      <View style={s.backdrop}>
        <View style={s.sheet}>
          <Image source={{uri: data.image}} style={s.headerImg} />

          <ScrollView contentContainerStyle={s.body}>
            <InfoCard icon="leaf" color="#2ecc71" title="Ingredients" text={data.ingredients} />
            <InfoCard icon="alert-circle" color="#f39c12" title="Allergen Information" text={data.allergens} />
            <InfoCard icon="fire" color="#e67e22" title="Nutritional Information"
              text={`Calories: ${data.nutrition.cal}\nServing Size: ${data.nutrition.size}\nPrep Time: ${data.nutrition.prep}\nSpice Level: ${data.nutrition.spice}`} />
            <InfoCard icon="food" color="#3498db" title="Dietary Information" text={data.dietary} />
          </ScrollView>

          <Pressable onPress={onClose} style={s.closeBtn} accessibilityLabel="Close info">
            <Text style={s.closeTxt}>× Close</Text>
          </Pressable>
        </View>
      </View>
    </Modal>
  );
}

const InfoCard = ({icon, color, title, text}:{icon:string;color:string;title:string;text:string;}) => (
  <View style={[s.card,{borderColor:color,backgroundColor:color+'15'}]}>
    <MCI name={icon as any} size={20} color={color} style={{marginRight:8}}/>
    <View style={{flex:1}}>
      <Text style={[s.cardTitle,{color}]}>{title}</Text>
      <Text style={s.cardBody}>{text}</Text>
    </View>
  </View>
);

const s = StyleSheet.create({
  backdrop:{flex:1,backgroundColor:'rgba(0,0,0,0.4)',justifyContent:'flex-end'},
  sheet:{backgroundColor:'#fff',borderTopLeftRadius:18,borderTopRightRadius:18,maxHeight:'90%'},
  headerImg:{width:'100%',height:200,borderTopLeftRadius:18,borderTopRightRadius:18},
  body:{padding:16},
  card:{flexDirection:'row',padding:12,borderWidth:1,borderRadius:12,marginBottom:12,alignItems:'flex-start'},
  cardTitle:{fontWeight:'700',marginBottom:4},
  cardBody:{color:'#555',fontSize:14,lineHeight:20},
  closeBtn:{alignItems:'center',paddingVertical:14,borderTopWidth:1,borderColor:'#eee'},
  closeTxt:{fontSize:16,fontWeight:'600'},
});
